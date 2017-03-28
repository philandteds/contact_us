<?php

/**
 * @package PT
 * Class EventListenerHelper
 * @date 11 Jun 2015
 */
class EventListenerHelper {

    const MAX_ZENDESK_RETRIES = 3;

    /**
     * @param $uri
     * @throws Exception
     */
    public static function handleCollectedInfo($uri)
    {
        if( $uri->element(0) === 'content' && $uri->element(1) === 'collectedinfo' ) {
            $node = eZContentObject::fetchByNodeID($uri->element(2));

            if ($node instanceof eZContentObject) {
                $collectionList = eZInformationCollection::fetchCollectionsList($node->ID);
                $collection     = $collectionList[count($collectionList)-1];
                if ($collection instanceof eZInformationCollection) {

                    $collectedInfo = self::buildCollectedInfoArray($collection);

                    $ini = eZINI::instance('site.ini');
                    $externalCareEmails = $ini->hasVariable('ContactUs', 'ExternalCareEmails') ?
                        $ini->variable('ContactUs', 'ExternalCareEmails') : array();					
                    $collectedCountry = $collectedInfo['country']['value'];

                    $queryEmailMap = $ini->hasVariable('ContactUs', 'QueryEmailMap') ?
                        $ini->variable('ContactUs', 'QueryEmailMap') : array();
                    $collectedQueryType = $collectedInfo['type_of_query']['value'];
					
                    $receiver = false;
					
					eZDebug::writeNotice( $queryEmailMap, "CONTACT US (queryEmailMap)" );
					
					if(array_key_exists($collectedQueryType, $queryEmailMap)){
						$receiver = $queryEmailMap[$collectedQueryType];
					}
					else if (array_key_exists($collectedCountry, $externalCareEmails)) {
                        $receiver = $externalCareEmails[$collectedCountry];
                    }

					eZDebug::writeNotice( "CONTACT US receiver= $receiver" );
					
                    if ($receiver) {
                        try {
                            static::sendCollectedInfoToEmail($receiver, $collection, $collectedInfo);
                        } catch(Exception $e) {

                        }
                    } else {
                        try {
                            //static::sendCollectedInfoToZD($collection, $collectedInfo);
                            self::sendCollectedInfoToZDWithRetry($collection->ID);
                        } catch(Exception $e){
                            $msg = $e->getMessage();
                            eZDebug::writeError("Failure to raise Zendesk ticket: $msg");
                        }
                    }
                }
            }
        }
    }



    static function sendCollectedInfoToZDWithRetry($collectionId) {
        $collection = eZInformationCollection::fetch($collectionId);
        $collectedInfo = self::buildCollectedInfoArray($collection);

        $retryTrackingRow = ptZendesk::fetch($collection->ID);
        if (!$retryTrackingRow) {
            $retryTrackingRow = new ptZendesk(array(
                'informationcollection_id' => $collectionId,
                'retry_count' => 0,
                'status' => ptZendesk::ZENDESK_RETRY_STATUS_PENDING
            ));
        }

        $retryCount = $retryTrackingRow->attribute('retry_count');
        $status = $retryTrackingRow->attribute('status');
        $error = null;

        $retryCount ++;

        try {
            self::sendCollectedInfoToZD($collection, $collectedInfo);

            // successful send.
            $status = ptZendesk::ZENDESK_RETRY_STATUS_SUCCESS;

        } catch (Exception $e) {

            // failure to send.
            $msg = $e->getMessage();
            $error = $msg;

            if ($retryCount >= self::MAX_ZENDESK_RETRIES) {
                $status = ptZendesk::ZENDESK_RETRY_STATUS_FAIL;

                self::sendZendeskFailureEmail($collection, $collectedInfo, $error);

            } else {
                $status = ptZendesk::ZENDESK_RETRY_STATUS_RETRY;
            }

        } finally {
            // update the tracking row.

            $retryTrackingRow->setAttribute('retry_count', $retryCount);
            $retryTrackingRow->setAttribute('status', $status);
            $retryTrackingRow->setAttribute('error', substr($error, 0, 1000));

            $retryTrackingRow->store();
        }

    }

    static function buildCollectedInfoArray(eZInformationCollection $collection) {
        $attributes = $collection->dataMap();
        $collectedInfo = array();

        foreach ($attributes as $key => $attribute) {
            $collectedInfo[$key] = array(
                'name'  => $attribute->contentClassAttributeName(),
                'value' => $attribute->attribute('data_text')
            );
        }

        return $collectedInfo;
    }

    /**
     * @param                         $receiver
     * @param eZInformationCollection $collection
     * @param array                   $collectedInfo
     */
    static function sendCollectedInfoToEmail(
        $receiver,
        eZInformationCollection $collection,
        array $collectedInfo
    ) {
        $attributes = $collection->dataMap();

        $emailSentAttribute = false;
        foreach ($attributes as $key => $attribute) {
            if ($key === 'is_email_sent') {
                $emailSentAttribute = $attribute;
                break;
            }
        }

        if ((int)$emailSentAttribute->attribute('data_int') === 0) {
            $ini  = eZINI::instance('site.ini');
            $mail = new eZMail();
            $tpl  = eZTemplate::factory();

            // set email sender
            $emailSender = $ini->variable('MailSettings', 'EmailSender');
            if (!$emailSender) {
                $emailSender = $ini->variable('MailSettings', 'AdminEmail');
            }
            $mail->setSender($emailSender);

            // set email receiver
            if (!$mail->validate($receiver)) {
                $receiver = $ini->variable('InformationCollectionSettings', 'EmailReceiver');
                if (!$receiver) {
                    $receiver = $ini->variable('MailSettings', 'AdminEmail');
                }
            }
            $mail->setReceiver($receiver);

            $dataMap = $collection->object()->dataMap();
            // set BCC receivers
            if ($dataMap['bcc_receivers']->attribute('has_content')) {
                $bccReceivers = explode(',', $dataMap['bcc_receivers']->attribute('content'));

                foreach ($bccReceivers as $bccReceiver) {
                    if ($mail->validate($bccReceiver)) {
                        $mail->addBcc($bccReceiver);
                    }
                }
            }

            // set email subject
            if ($dataMap['email_subject_prefix']->attribute('has_content')) {
                $subject = '[' . $dataMap['email_subject_prefix']->attribute('content') . ']';
            } else {
                $subject = $collection->object()->Name;
            }

            if ((bool)$collectedInfo['subject']) {
                $subject .= ' ' . $collectedInfo['subject']['value'];
            }
            $mail->setSubject($subject);
            $collectedInfo['subject']['value'] = $subject;

            $tpl->setVariable('collected_info', $collectedInfo);
            $templateResult = $tpl->fetch('design:mail/feedback_form.tpl');

            $mail->setBody($templateResult);
            $mailResult = eZMailTransport::send($mail);

            if ($mailResult === false) {
                throw new Exception('Mail with collected info has not been sent');
            } else {
                $emailSentAttribute->setAttribute('data_int', 1);
                $emailSentAttribute->store();
            }
        }
    }

    /**
     * @param eZInformationCollection $collection
     * @param array                   $collectedInfo
     * @throws Exception
     */
    static function sendCollectedInfoToZD(
        eZInformationCollection $collection,
        array $collectedInfo
    ) {
        $attributes = $collection->dataMap();

        $ticketIDAttribute = false;
        foreach ($attributes as $key => $attribute) {
            if ($key === 'zendesk_ticket_id') {
                $ticketIDAttribute = $attribute;
                break;
            }
        }

        if ((int)$ticketIDAttribute->attribute('data_text') === 0) {
            // Extract data from contact us form collection
            $fields = array(
                'name'    => $collectedInfo['first_name']['value'],
                'country' => strtolower($collectedInfo['country']['value']),
                'phone'   => $collectedInfo['phone']['value']
            );

            $dataMap = $collection->object()->dataMap();
            if ($dataMap['email_subject_prefix']->attribute('has_content')) {
                $subject = '[' . $dataMap['email_subject_prefix']->attribute('content') . ']';
            } else {
                $subject = $collection->object()->Name;
            }

            if ((bool)$collectedInfo['subject']) {
                $subject .= ' ' . $collectedInfo['subject']['value'];
            }

            $message = $collectedInfo['message']['value'];
            // email of contact us collection
            $email = $collectedInfo['email']['value'];
            $name  = $collectedInfo['first_name']['value'];

            // Get instance of ZD API wrapper
            $api = ZendeskAPIWrapper::instance();
            $ini = $api->getIni();

            // Fetch user by email from ZD
            $user = $api->searchUserByEmail($email);
            if ($user === null) {
                // Create user, if it does not exist in ZD
                $user = $api->createUser($name, $email);
            }

            // We can not continue, if user was not created in ZD
            if ($user === null) {
                throw new Exception('User ID can not be fetched from Zendesk');
            }
            // Handle custom_fields
            $fieldsMap    = $ini->variable('ContactUs', 'Fields');
            $mappedFields = array();
            foreach ($fieldsMap as $field => $id) {
                if (isset($fields[$field])) {
                    $mappedFields[] = array('id' => $id, 'value' => $fields[$field]);
                }
            }
            // Create ZD ticket
            $params = array(
                'custom_fields'  => $mappedFields,
                'description'    => $message,
                'subject'        => $subject,
                'tags'           => $ini->variable('Recalls', 'Tags'),
                'status'         => $ini->variable('Recalls', 'Status'),
                'type'           => $ini->variable('Recalls', 'Type'),
                'ticket_form_id' => $ini->variable('ContactUs', 'TicketFormID'),
                'requester_id'   => $user->id,
                'group_id'       => ''
            );
            if ($ini->hasVariable('Tickets', 'BrandID') && strlen($ini->variable('Tickets', 'BrandID')) > 0) {
                $params['brand_id'] = $ini->variable('Tickets', 'BrandID');
            }

            $ticket = $api->createTicket($params);

            if ($ticket === null) {
                throw new Exception('Zendesk ticket was not created');
            } else {
                $ticketIDAttribute->setAttribute('data_text', $ticket->id);
                $ticketIDAttribute->store();
            }
        }
    }

    private static function sendZendeskFailureEmail($collection, $collectedInfo, $error)
    {

        $ini  = eZINI::instance('site.ini');
        $mail = new eZMail();
        $tpl  = eZTemplate::factory();

        // set email sender
        $emailSender = $ini->variable('MailSettings', 'EmailSender');
        if (!$emailSender) {
            $emailSender = $ini->variable('MailSettings', 'AdminEmail');
        }
        $mail->setSender($emailSender);

        // set email receiver

        $receiver = $ini->variable('InformationCollectionSettings', 'EmailReceiver');
        if (!$receiver) {
            $receiver = $ini->variable('MailSettings', 'AdminEmail');
        }
        $mail->setReceiver($receiver);

        $mail->setSubject('FAIL: Cannot create a Zendesk ticket for a customer support request');

        $tpl->setVariable('collected_info', $collectedInfo);
        $tpl->setVariable( 'collection_id', $collection->ID);
        $tpl->setVariable('zendesk_error', $error);

        $templateResult = $tpl->fetch('design:mail/zendesk_failure_fallback.tpl');

        $mail->setBody($templateResult);
        $mailResult = eZMailTransport::send($mail);

        if ($mailResult === false) {
            throw new Exception('Mail with collected info has not been sent');
        }

    }
}
