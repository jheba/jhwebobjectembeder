{def $webobjectdata = jhwebobjecturlhandler($webobjectUrl, $size)}
{*$webobjectdata|attribute(show, 2)*}
{if array('unknown_platform','not_supported_platform')|contains( $webobjectdata.platform )|not}
<div class="webobject-container">
    {include uri=concat("design:content/datatype/view/ezxmltags/webobject_", $webobjectdata.platform|downcase(), ".tpl") webobject_id=$webobjectdata.id dimension=$webobjectdata.dimension caption=$caption extra=$webobjectdata.parameters}
</div>
{/if}
