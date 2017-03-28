<h1>Collection {$information_collection_id} has been transmitted to Zendesk</h1>

{if $error|not}
    <h2>Success.</h2>
    <br/><br/>
{else}
    <h2>Failure:</h2>
    <pre>
        <br/>
        {$error}
        <br/>
    </pre>
{/if}


<a href={concat('/infocollector/collectionlist/', $contentobject_id, '/(offset)/', $offset)|ezurl}>Return to list</a>

