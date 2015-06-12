<?php

/**
 * @package PT
 * Class EventListenerHelper
 * @date 11 Jun 2015
 */
class EventListenerHelper {

    /**
     * @param $uri
     * @throws Exception
     */
    public static function handleCollectedInfo($uri)
    {
        if( $uri->element(0) === 'content' && $uri->element(1) === 'collectedinfo' ) {
            $node = eZContentObject::fetchByNodeID($uri->element(2));

            if ($node instanceof eZContentObject) {
                $collectionList = eZInformationCollection::fetchCollectionsList($node->attribute('contentobject_id'));
                $collection     = $collectionList[count($collectionList)-1];
                if ($collection instanceof eZInformationCollection) {
                    $attributes = $collection->informationCollectionAttributes();

                    foreach ($attributes as $key => $attribute) {
                        $collectedInfo[$attribute->contentClassAttributeName()] = $attribute->attribute('data_text');
                    }

                    $ini = eZINI::instance('site.ini');
                    $externalCareEmails = $ini->hasVariable('ContactUs', 'ExternalCareEmails') ?
                        $ini->variable('ContactUs', 'ExternalCareEmails') : array();
                    $collectedCountry = $collectedInfo['Country'];

                    $receiver = false;
                    if (array_key_exists($collectedCountry, $externalCareEmails)) {
                        $receiver = $externalCareEmails[$collectedCountry];
                    }

                    if ($receiver) {
                        try {
                            static::sendCollectedInfoToEmail($receiver, $collection, $collectedInfo);
                        } catch(Exception $e) {
                            //var_dump($e->getMessage());
                        }
                    } else {
                        try {
                            static::sendCollectedInfoToZD($collection, $collectedInfo);
                        } catch(Exception $e){
                            //var_dump($e->getMessage());
                        }
                    }
                }
            }
        }
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
        $ini  = eZINI::instance('site.ini');
        $mail = new eZMail();
        $tpl  = eZTemplate::factory();
        $tpl->setVariable('collected_info', $collectedInfo);
        $templateResult = $tpl->fetch( 'design:mail/feedback_form.tpl' );

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
        if ( $dataMap['bcc_receivers']->attribute('has_content') ) {
            $bccReceivers = explode(',', $dataMap['bcc_receivers']->attribute('content'));

            foreach ( $bccReceivers as $bccReceiver ) {
                if ( $mail->validate( $bccReceiver ) ) {
                    $mail->addBcc( $bccReceiver );
                }
            }
        }

        // set email subject
        if ($dataMap['email_subject_prefix']->attribute('has_content')) {
            $subject = '[' . $dataMap['email_subject_prefix']->attribute('content') . ']';
        } else {
            $subject = $collection->object()->Name;
        }

        if ((bool)$collectedInfo['Subject']) {
            $subject .= ' ' . $collectedInfo['Subject'];
        }
        $mail->setSubject($subject);

        $mail->setBody($templateResult);
        $mailResult = eZMailTransport::send($mail);

        if( $mailResult === false ) {
            throw new Exception( 'Mail with collected info has not been sent' );
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
        // Extract data from contact us form collection
        $fields  = array(
            'name'    => $collectedInfo['Name'],
            'country' => strtolower($collectedInfo['Country']),
            'phone'   => $collectedInfo['Phone']
        );

        $dataMap = $collection->object()->dataMap();
        if ($dataMap['email_subject_prefix']->attribute('has_content')) {
            $subject = '[' . $dataMap['email_subject_prefix']->attribute('content') . ']';
        } else {
            $subject = '[Contact US]';
        }
        $message = $collectedInfo['Message'];
        // email of contact us collection
        $email   = $collectedInfo['Email'];
        $name    = $collectedInfo['Name'];

        // Get instance of ZD API wrapper
        $api = ZendeskAPIWrapper::instance();
        $ini = $api->getIni();

        // Fetch user by email from ZD
        $user = $api->searchUserByEmail( $email );
        if( $user === null ) {
            // Create user, if it does not exist in ZD
            $user = $api->createUser( $name, $email );
        }
        // We can not continue, if user was not created in ZD
        if( $user === null ) {
            throw new Exception( 'User ID can not be fetched from Zendesk' );
        }
        // Handle custom_fields
        $fieldsMap    = $ini->variable( 'ContactUs', 'Fields' );
        $mappedFields = array();
        foreach($fieldsMap as $field => $id) {
            if(isset($fields[$field])) {
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
        if( $ini->hasVariable('Tickets', 'BrandID') && strlen($ini->variable('Tickets', 'BrandID') ) > 0) {
            $params['brand_id'] = $ini->variable('Tickets', 'BrandID');
        }
        $ticket = $api->createTicket( $params );
        if( $ticket === null ) {
            throw new Exception('Zendesk ticket was not created');
        }
    }
}
