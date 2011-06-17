<object width="{$dimension.width}" height="{$dimension.height}"><param name="movie" value="http://photopeach.com/public/swf/story.swf"></param><param name="allowscriptaccess" value="always"/><param name="allowfullscreen" value="true"/><param name="flashvars" value="photos=http://photopeach.com%2Fapi%2Fgetphotos%3Falbum_id%3D{$webobject_id}&autoplay=0&embed=1"/><embed src="http://photopeach.com/public/swf/story.swf" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="{$dimension.width}" height="{$dimension.height}" flashvars="photos=http://photopeach.com%2Fapi%2Fgetphotos%3Falbum_id%3D{$webobject_id}&autoplay=0&embed=1"></embed></object>
{if is_set( $caption )}
<p class="webobject-caption">{$caption}</p>
{/if}
