<?php
interface jhWebobjectUrlHandlerInterface
{
    function getPlatform();
    function getObjectID();
    function getParsedURL();
    function setSize( $size );
    function getSize();
    function getArray();
}

abstract class jhWebobjectUrlHandlerAbstract implements jhWebobjectUrlHandlerInterface
{
    protected $parsedObjectURL;
    protected $platform;
    protected $objectID;
    protected $chosenSize;
    protected $width;
    protected $height;
    public $availableSizes;

    final function __construct( $parsedURL, $platform, $availableSizes=array() )
    {
        $this->parsedObjectURL = $parsedURL;
        $this->platform = $platform;
        $this->availableSizes = $availableSizes;
        eZDebug::writeDebug( $this->platform, 'jhwebobjectembeder - chosen size' );
        eZDebug::writeDebug( $this->availableSizes, 'jhwebobjectembeder - available sizes' );
    }

    final function getPlatform()
    {
        return $this->platform;
    }

    final function getParsedURL()
    {
        return $this->parsedObjectURL;
    }

    final function setSize( $size='small' )
    {
        $this->chosenSize = array_key_exists( $size, $this->availableSizes )?$this->availableSizes[$size]:$this->availableSizes['default'];
        eZDebug::writeDebug( $this->chosenSize, 'jhwebobjectembeder - chosen size' );
    }

    final function getSize()
    {
        list( $this->width, $this->height ) = explode( 'x', $this->chosenSize );
        return array( 'width' => $this->width,
                      'height' => $this->height );
    }

    final function getArray()
    {
        return array(
            'id'  => $this->getObjectID(),
            'platform' => $this->getPlatform(),
            //'parsedObjectURL' => $this->getParsedURL(),
            'dimension' => $this->getSize(),
            );
    }
}

class jhWebobjectUrlHandler
{
    //protected $objectURL;
    protected $parsedObjectURL;
    protected $platform;
    protected $platformObject;
    private $webobjectIni;

    static function getWebobject( $url )
    {
        $webobjectIni = eZINI::instance( 'webobject.ini' );
        $parsedObjectURL = parse_url( $url );
        if ( $webobjectIni->hasVariable( 'WebobjectPlatforms', 'Platforms' ) )
        {
            $platforms = $webobjectIni->variable( 'WebobjectPlatforms', 'Platforms' );
        }
        else
        {
            $platforms = array();
        }

        $currentPlatform = 'unknown_platform';
        $hosts = array();
        //eZDebug::writeDebug( $platforms, 'jhwebobjectembeder - platforms' );
        foreach( $platforms as $platform)
        {
            if( $webobjectIni->hasVariable( $platform, 'Hosts' ) )
            {
                $hosts[$platform] = $webobjectIni->variable( $platform, 'Hosts' );
                //eZDebug::writeDebug( $hosts[$platform], 'jhwebobjectembeder - hosts' );
                if( in_array( $parsedObjectURL['host'], $hosts[$platform] ) )
                {
                    $currentPlatform = $platform;
                    if( $webobjectIni->hasVariable( $platform, 'Size' ) )
                    {
                        $availableSizes = $webobjectIni->variable( $platform, 'Size' );
                    }
                }
            }
            else $hosts[$platform] = array();
        }

        if( !isset( $availableSizes ) )
        {
            $availableSizes = array( 'default' => '320x200' );
        }

        $platformClassName = 'jhWebobjectUrlHandler_'.$currentPlatform;
        if( !class_exists( $platformClassName ) )
        {
            eZDebug::writeError( 'Class '.$platformClassName.' required by platform "'.$currentPlatform.'" defined in '.$webobjectIni->filename().' does not exist.', 'jhwebobjectembeder extension' );
            $platformClassName = 'jhWebobjectUrlHandler_not_supported_platform';
            $currentPlatform = 'not_supported_platform';
        }
        $platformObject = new $platformClassName( $parsedObjectURL, $currentPlatform, $availableSizes );
        return $platformObject;
    }
}

class jhWebobjectUrlHandler_youtube extends jhWebobjectUrlHandlerAbstract
{
    function getObjectID()
    {
        if( $this->parsedObjectURL['path'] == '/watch' )
        {
            $urlParts = explode( '&', $this->parsedObjectURL['query'] );
            $this->objectID = '/'.ltrim( $urlParts[0], 'v=' );
        }
        else
        {
            $this->objectID = $this->parsedObjectURL['path'];
        }
        return $this->objectID;
    }
}

class jhWebobjectUrlHandler_slideshare extends jhWebobjectUrlHandlerAbstract
{
    function getObjectID()
    {
        list( $user, $title_and_id ) = explode( '/', ltrim( $this->parsedObjectURL['path'], '/' ) );
        $parts = array_reverse( explode( '-', $title_and_id ) );
        foreach( $parts as $part )
        {
            if( is_numeric( $part ) )
                return $part;
        }
        eZDebug::writeError( 'Cannot find an ID for an object of '.$this->getPlatform(), 'jhwebobjectembeder - getObjectID' );
        return $id;
    }
}

class jhWebobjectUrlHandler_vimeo extends jhWebobjectUrlHandlerAbstract
{
    function getObjectID()
    {
        return $this->parsedObjectURL['path'];
    }
}

class jhWebobjectUrlHandler_unknown_platform extends jhWebobjectUrlHandlerAbstract
{
    function getObjectID()
    {
        return 'Content provided by '.$this->parsedObjectURL['host'].' is not supported or its support is disabled.';
    }
}

class jhWebobjectUrlHandler_not_supported_platform extends jhWebobjectUrlHandlerAbstract
{
    function getObjectID()
    {
        return 'Requested platform is defined in webobject.ini but associated class cannot be found.';
    }
}

class jhWebobjectUrlHandler_photopeach extends jhWebobjectUrlHandlerAbstract
{
    function getObjectID()
    {
        list( $album, $album_id ) = explode( '/', ltrim( $this->parsedObjectURL['path'], '/' ) );
        return $album_id;
    }
}

?>
