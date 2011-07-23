<?php
interface jhWebobjectUrlHandlerInterface
{
    function getPlatform();
    function getObjectID();
    function getParsedURL();
    function setSize( $size );
    function getSize();
    function getArray();
    function getParameterArray();
    function appendParameter( $key, $value );
    function getParameter( $key );
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
    protected $parameterArray;

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
        $array =  array(
            'id'  => $this->getObjectID(),
            'platform' => $this->getPlatform(),
            //'parsedObjectURL' => $this->getParsedURL(),
            'dimension' => $this->getSize(),
            'parameters' => $this->getParameterArray(),
            );
        eZDebug::writeDebug( $array, 'jh webobjectembeder array' );
        return $array;
    }
    
    final function getParameterArray()
    {
        return $this->parameterArray;
    }

    final function appendParameter( $key, $value )
    {
        if( isset( $key ) )
        {
            $this->parameterArray[$key] = $value;
            return true;
        }
        else return false;
    }

    final function getParameter( $key )
    {
        if( array_key_exists( $key, $this->parameterArray ) )
        {
            return $this->parameterArray[$key];
        }
        else return null;
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

class jhWebobjectUrlHandler_yr extends jhWebobjectUrlHandlerAbstract
{
    /**
     * http://www.yr.no/place/Norway/Akershus/Nittedal/Varingskollen_alpinsenter/
     * http://www.yr.no/place/[Country]/[Region]/[City]/[Place]/
     */
    function getObjectID()
    {
        $pathArray = explode( '/', ltrim( $this->parsedObjectURL['path'], '/' ) );
        if( end( $pathArray ) == 'hour_by_hour.html' )
        {
            $this->appendParameter( 'defaultView', 'hour_by_hour' );
            array_pop( $pathArray );
            return implode( '/', $pathArray );
        }
        else
        {
            $this->appendParameter( 'defaultView', 'small' );
            return rtrim( $this->parsedObjectURL['path'], '/' );
        }
    }
}

class jhWebobjectUrlHandler_googlecalendar extends jhWebobjectUrlHandlerAbstract
{
    /**
     * https://www.google.com/calendar/embed?height=600&amp;wkst=1&amp;bgcolor=%23FFFFFF&amp;src=CALENDAR_SOURCE_ID&amp;color=%23691426&amp;ctz=Europe%2FCopenhagen
     * https://www.google.com/calendar/embed?src=CALENDAR_SOURCE_ID&ctz=Europe/Copenhagen
     */
    function getObjectID()
    {
        $this->objectID = 'default';
        eZDebug::writeDebug( $this->parsedObjectURL['query'], 'jhwebobjectembeder - query' );
        if( $this->parsedObjectURL['path'] == '/calendar/embed' )
        {
            $urlParts = explode( '&', $this->parsedObjectURL['query'] );
            foreach( $urlParts as $urlPart )
            {
                eZDebug::writeDebug( $urlPart, 'jhwebobjectembeder - urlPart' );
                if( substr( $urlPart, 0, 4 ) == 'src=' )
                {
                    $this->objectID = ltrim( $urlPart, 'src=' );
                }
            }
            // TODO: test if objectID is set; if not, show error message
        }
        else
        {
            return 'The google calendar URI provided to the object, is not correct.';
        }
        return $this->objectID;
    }
}

?>
