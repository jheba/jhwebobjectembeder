{def $webobjectdata = jhwebobjecturlhandler($webobjectUrl, $size)}
<div class="webobject-container-admin" style="width: {$webobjectdata.dimension.width}px; height: {$webobjectdata.dimension.height}px;">
<p>embedded object from <em>{$webobjectdata.platform}</em> platform</p>
</div>
