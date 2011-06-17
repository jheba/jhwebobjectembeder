<?php

/*!
  \class   jhwebobjecturlhandlerTemplateOperator jhwebobjecturlhandlertemplateoperator.php
  \ingroup eZTemplateOperators
  \brief   Handles template operator jhwebobjecturlhandler. By using jhwebobjecturlhandler you can extract essential data from an URL to a media object such as youtube/vimeo video, slideshow from slideshare.net etc.
  \version 1.0
  \date    Sunday 29 May 2011 10:42:40 pm
  \author  Jaroslaw Heba

  

  Example:
\code
{def $webobjectdata = jhwebobjecturlhandler( $webobjectURL, $size )}
<iframe width="{$webobjectdata.dimension.width}" height="{$webobjectdata.dimension.height}" src="http://www.youtube.com/embed{$webobjectdata.id}?rel=0" frameborder="0" allowfullscreen></iframe>
\endcode
*/


class jhwebobjecturlhandlerTemplateOperator
{
    /*!
      Constructor, does nothing by default.
    */
    function jhwebobjecturlhandlerTemplateOperator()
    {
    }

    /*!
     \return an array with the template operator name.
    */
    function operatorList()
    {
        return array( 'jhwebobjecturlhandler' );
    }

    /*!
     \return true to tell the template engine that the parameter list exists per operator type,
             this is needed for operator classes that have multiple operators.
    */
    function namedParameterPerOperator()
    {
        return true;
    }

    /*!
     See eZTemplateOperator::namedParameterList
    */
    function namedParameterList()
    {
        return array( 'jhwebobjecturlhandler' => array( 'web_url' => array( 'type' => 'string',
                                                                                'required' => true,
                                                                                'default' => 'default text' ),
                                                        'extra_param' => array( 'type' => 'string',
                                                                                 'required' => false,
                                                                                 'default' => 'small' ) ) );
    }


    /*!
     Executes the PHP function for the operator cleanup and modifies \a $operatorValue.
    */
    function modify( $tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters, $placement )
    {
        $webUrl = $namedParameters['web_url'];
        $extraParam = $namedParameters['extra_param'];
        //$parsedObjectUrl = parse_url( $webUrl );

        $webObject = jhWebobjectUrlHandler::getWebobject( $webUrl );
        
        // Example code. This code must be modified to do what the operator should do. Currently it only trims text.
        switch ( $operatorName )
        {
            case 'jhwebobjecturlhandler':
            {
                switch( $extraParam )
                {
                    case 'webobject_platform':
                    {
                         $operatorValue = $webObject->getPlatform();
                    } break;
                    case 'object_id':
                    {
                         $operatorValue = $webObject->getObjectID();
                    } break;
                    default:
                    {
                        $webObject->setSize( $extraParam );
                        $operatorValue = $webObject->getArray();
                    }
                }
            } break;
        }
    }
}

?>
