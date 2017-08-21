<?php

/**
 * Created by PhpStorm.
 * User: roberto
 * Date: 23/02/15
 * Time: 18.40
 */
class engineController extends ajaxController {

    private $exec;
    private $provider;
    private $id;
    private $name;
    private $engineData;

    /**
     * @var Features
     */
    private $feature_set;

    private static $allowed_actions = array(
            'add', 'delete', 'execute'
    );
    private static $allowed_execute_functions = array(
        'letsmt' => array('getTermList')
    );

    public function __construct() {

        parent::__construct();

        //Session Enabled
        $this->checkLogin();
        //Session Disabled

        $filterArgs = array(
                'exec'      => array(
                        'filter'  => FILTER_SANITIZE_STRING,
                        'flags'   => FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_LOW
                ),
                'id' => array(
                        'filter'  => FILTER_SANITIZE_NUMBER_INT
                ),
                'name'      => array(
                        'filter'  => FILTER_SANITIZE_STRING,
                        'flags'   => FILTER_FLAG_STRIP_LOW
                ),
                'data'    => array(
                        'filter'  => FILTER_SANITIZE_STRING,
                        'flags'   => FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_NO_ENCODE_QUOTES
                ),
                'provider'  => array(
                        'filter'  => FILTER_SANITIZE_STRING,
                        'flags'   => FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_LOW
                )
        );

        $postInput = filter_input_array( INPUT_POST, $filterArgs );

        $this->exec         = $postInput[ 'exec' ];
        $this->id           = $postInput[ 'id' ];
        $this->name         = $postInput[ 'name' ];
        $this->provider     = $postInput[ 'provider' ];
        $this->engineData   = json_decode( $postInput[ 'data' ], true );

        if ( is_null( $this->exec ) ) {
            $this->result[ 'errors' ][ ] = array( 'code' => -1, 'message' => "Exec field required" );

        }

        else if ( !in_array( $this->exec, self::$allowed_actions ) ) {
            $this->result[ 'errors' ][ ] = array( 'code' => -2, 'message' => "Exec value not allowed" );
        }

        //ONLY LOGGED USERS CAN PERFORM ACTIONS ON KEYS
        if ( !$this->userIsLogged ) {
            $this->result[ 'errors' ][ ] = array(
                    'code'    => -3,
                    'message' => "Login is required to perform this action"
            );
        }

        $this->feature_set = new FeatureSet();

    }

    /**
     * When Called it perform the controller action to retrieve/manipulate data
     *
     * @return mixed
     */
    public function doAction() {
        if ( count( $this->result[ 'errors' ] ) > 0 ) {
            return;
        }

        switch ( $this->exec ) {
            case 'add':
                $this->add();
                break;
            case 'delete':
                $this->disable();
                break;
            case 'execute':
                $this->execute();
                break;
            default:
                break;
        }

    }

    /**
     * This method adds an engine in a user's keyring
     */
    private function add() {

        $newEngineStruct = null;
        $validEngine = true;

        switch ( strtolower( $this->provider ) ) {
            case strtolower( Constants_Engines::MICROSOFT_HUB ):

                /**
                 * Create a record of type MicrosoftHub
                 */
                $newEngineStruct = EnginesModel_MicrosoftHubStruct::getStruct();

                $newEngineStruct->name                                = $this->name;
                $newEngineStruct->uid                                 = $this->uid;
                $newEngineStruct->type                                = Constants_Engines::MT;
                $newEngineStruct->extra_parameters[ 'client_id' ]     = $this->engineData['client_id'];
                $newEngineStruct->extra_parameters[ 'client_secret' ] = $this->engineData['secret'];
                $newEngineStruct->extra_parameters[ 'category' ]      = $this->engineData['category'];

                break;
            case strtolower( Constants_Engines::MOSES ):
            case strtolower( Constants_Engines::TAUYOU ):

                /**
                 * Create a record of type Moses
                 */
                $newEngineStruct = EnginesModel_MosesStruct::getStruct();

                $newEngineStruct->name                                = $this->name;
                $newEngineStruct->uid                                 = $this->uid;
                $newEngineStruct->type                                = Constants_Engines::MT;
                $newEngineStruct->base_url                            = $this->engineData[ 'url' ];
                $newEngineStruct->extra_parameters[ 'client_secret' ] = $this->engineData[ 'secret' ];

                break;

            case strtolower( Constants_Engines::IP_TRANSLATOR ):

                /**
                 * Create a record of type IPTranslator
                 */
                $newEngineStruct = EnginesModel_IPTranslatorStruct::getStruct();

                $newEngineStruct->name                                = $this->name;
                $newEngineStruct->uid                                 = $this->uid;
                $newEngineStruct->type                                = Constants_Engines::MT;
                $newEngineStruct->extra_parameters[ 'client_secret' ] = $this->engineData[ 'secret' ];

                break;

            case strtolower( Constants_Engines::DEEPLINGO ):

                /**
                 * Create a record of type IPTranslator
                 */
                $newEngineStruct = EnginesModel_DeepLingoStruct::getStruct();

                $newEngineStruct->name                                = $this->name;
                $newEngineStruct->uid                                 = $this->uid;
                $newEngineStruct->type                                = Constants_Engines::MT;
                $newEngineStruct->base_url                            = $this->engineData[ 'url' ];
                $newEngineStruct->extra_parameters[ 'client_secret' ] = $this->engineData[ 'secret' ];

                break;
            case strtolower( Constants_Engines::APERTIUM ):

                /**
                 * Create a record of type APERTIUM
                 */
                $newEngineStruct = EnginesModel_ApertiumStruct::getStruct();

                $newEngineStruct->name                                = $this->name;
                $newEngineStruct->uid                                 = $this->uid;
                $newEngineStruct->type                                = Constants_Engines::MT;
                $newEngineStruct->extra_parameters[ 'client_secret' ] = $this->engineData[ 'secret' ];

                break;

            case strtolower( Constants_Engines::ALTLANG ):

                /**
                 * Create a record of type ALTLANG
                 */
                $newEngineStruct = EnginesModel_AltlangStruct::getStruct();

                $newEngineStruct->name                                = $this->name;
                $newEngineStruct->uid                                 = $this->uid;
                $newEngineStruct->type                                = Constants_Engines::MT;
                $newEngineStruct->extra_parameters[ 'client_secret' ] = $this->engineData[ 'secret' ];

                break;

            case strtolower( Constants_Engines::LETSMT ):

                /**
                 * Create a record of type LetsMT
                 */
                $newEngineStruct = EnginesModel_LetsMTStruct::getStruct();

                $newEngineStruct->name                                = $this->name;
                $newEngineStruct->uid                                 = $this->uid;
                $newEngineStruct->type                                = Constants_Engines::MT;
                $newEngineStruct->extra_parameters[ 'client_id' ]     = $this->engineData['client_id'];
                $newEngineStruct->extra_parameters[ 'system_id' ]     = $this->engineData[ 'system_id' ]; // whether this has been set or not indicates whether we should
                // return the newly added system's id or the list of available systems
                // for the user to choose from. the check happens later on
                $newEngineStruct->extra_parameters[ 'terms_id' ]      = $this->engineData[ 'terms_id' ];
                $newEngineStruct->extra_parameters[ 'use_qe' ]        = $this->engineData[ 'use_qe' ];
                $newEngineStruct->extra_parameters[ 'source_lang' ]   = $this->engineData[ 'source_lang' ];
                $newEngineStruct->extra_parameters[ 'target_lang' ]   = $this->engineData[ 'target_lang' ];

                if ($newEngineStruct->extra_parameters[ 'use_qe' ]) {
                    $minQEString = $this->engineData[ 'minimum_qe' ];
                    if (!is_numeric($minQEString)) {
                        $this->result[ 'errors' ][ ] = array( 'code' => -13, 'message' => "Minimum QE score should be a number between 0 and 1." );
                        return;
                    }
                    $minimumQEScore = floatval($minQEString);
                    if ($minimumQEScore < 0 || $minimumQEScore > 1) {
                        $this->result[ 'errors' ][ ] = array( 'code' => -13, 'message' => "Minimum QE score should be a number between 0 and 1." );
                        return;
                    }
                    $newEngineStruct->extra_parameters[ 'minimum_qe' ] = $minimumQEScore;
                }

                break;

            case strtolower( Constants_Engines::SMART_MATE ):

                /**
                 * Create a record of type IPTranslator
                 */
                $newEngineStruct = EnginesModel_SmartMATEStruct::getStruct();

                $newEngineStruct->name                                = $this->name;
                $newEngineStruct->uid                                 = $this->uid;
                $newEngineStruct->type                                = Constants_Engines::MT;
                $newEngineStruct->extra_parameters[ 'client_id' ]     = $this->engineData[ 'client_id' ];
                $newEngineStruct->extra_parameters[ 'client_secret' ] = $this->engineData[ 'secret' ];

                break;

            case strtolower( Constants_Engines::YANDEX_TRANSLATE ):

                /**
                 * Create a record of type YandexTranslate
                 */
                $newEngineStruct = EnginesModel_YandexTranslateStruct::getStruct();

                $newEngineStruct->name                                = $this->name;
                $newEngineStruct->uid                                 = $this->uid;
                $newEngineStruct->type                                = Constants_Engines::MT;
                $newEngineStruct->extra_parameters[ 'client_secret' ] = $this->engineData[ 'secret' ];

                break;

            case strtolower( Constants_Engines::MMT ):

                //TODO Move this piece of code in the plugin

                /**
                 * Create a record of type MMT
                 */
                $newEngineStruct = EnginesModel_MMTStruct::getStruct();

                $newEngineStruct->name                                   = $this->name;
                $newEngineStruct->uid                                    = $this->uid;
                $newEngineStruct->type                                   = Constants_Engines::MT;
                $newEngineStruct->extra_parameters[ 'MyMemory-License' ] = $this->engineData[ 'secret' ];
                $newEngineStruct->extra_parameters[ 'User_id' ]          = $this->userMail;

                break;

            default:
                $validEngine = false;
        }

        if( !$validEngine ){
            $this->result[ 'errors' ][ ] = array( 'code' => -4, 'message' => "Engine not allowed" );
            return;
        }

        $engineList = $this->feature_set->filter( 'getAvailableEnginesListForUser', Constants_Engines::getAvailableEnginesList(), $this->logged_user );

        $engineDAO = new EnginesModel_EngineDAO( Database::obtain() );
        $result = null;
        $newTestCreatedMT = null;
        if( array_search( $newEngineStruct->class_load, $engineList ) ){
            $result = $engineDAO->create( $newEngineStruct );
        }

        if(! $result instanceof EnginesModel_EngineStruct){
            $this->result[ 'errors' ][ ] = array( 'code' => -9, 'message' => "Creation failed. Generic error" );
            return;
        }

        if( $newEngineStruct instanceof EnginesModel_MicrosoftHubStruct ){

            $newTestCreatedMT = Engine::getInstance( $result->id );
            $config = $newTestCreatedMT->getConfigStruct();
            $config[ 'segment' ] = "Hello World";
            $config[ 'source' ]  = "en-US";
            $config[ 'target' ]  = "fr-FR";

            $mt_result = $newTestCreatedMT->get( $config );

            if ( isset( $mt_result['error']['code'] ) ) {
                $this->result[ 'errors' ][ ] = $mt_result['error'];
                $engineDAO->delete( $result );
                return;
            }

        } elseif ( $newEngineStruct instanceof EnginesModel_IPTranslatorStruct ){

            $newTestCreatedMT = Engine::getInstance( $result->id );

            /**
             * @var $newTestCreatedMT Engines_IPTranslator
             */
            $config = $newTestCreatedMT->getConfigStruct();
            $config[ 'source' ]  = "en-US";
            $config[ 'target' ]  = "fr-FR";

            $mt_result = $newTestCreatedMT->ping( $config );

            if ( isset( $mt_result['error']['code'] ) ) {
                $this->result[ 'errors' ][ ] = $mt_result['error'];
                $engineDAO->delete( $result );
                return;
            }

        } elseif ( $newEngineStruct instanceof EnginesModel_LetsMTStruct && empty($this->engineData[ 'system_id' ])){
            // the user has not selected a translation system. only the User ID and the engine's name has been entered
            // get the list of available systems and return it to the user

            $newTestCreatedMT = Engine::getInstance( $result->id );
            $config = $newTestCreatedMT->getConfigStruct();
            $systemList = $newTestCreatedMT->getSystemList($config);

            $engineDAO->delete($result); // delete the newly added engine. this is the first time in engineController::add()
                                         // and the user has not yet selected a translation system
            if ( isset( $systemList['error']['code'] ) ) {
                $this->result[ 'errors' ][ ] = $systemList['error'];
                return;
            }

            $uiConfig = array(
                'client_id' => array('value' => $this->engineData['client_id']),
                'system_id' => array(),
                'terms_id' => array()
            );
            foreach ($systemList as $systemID => $systemInfo){
                $uiConfig['system_id'][$systemID] = array('value' => $systemInfo['name'],
                                                          'data'  => $systemInfo['metadata']
                                                    );
            }

            $this->result['name'] = $this->name;
            $this->result['data']['config'] = $uiConfig;
        } elseif ( $newEngineStruct instanceof EnginesModel_LetsMTStruct){
            // The user has added and configured the Tilde MT engine (the System ID has been set)
            // Do a simple translation request so that the system wakes up by the time the user needs it for translating
            $newTestCreatedMT = Engine::getInstance( $result->id );
            $newTestCreatedMT->wakeUp();
        } elseif( $newEngineStruct instanceof EnginesModel_MMTStruct ){

            //TODO Move this piece of code in the plugin

            $newTestCreatedMT = Engine::getInstance( $result->id );
            /**
             * @var $newTestCreatedMT Engines_MMT
             */
            $mt_result = $newTestCreatedMT->checkAccount()->get_as_array();

            if ( isset( $mt_result['error']['code'] ) ) {
                $this->result[ 'errors' ][ ] = $mt_result['error'];
                $engineDAO->delete( $result );
                return;
            }

        }

        $this->feature_set->run( 'postEngineCreation', $newTestCreatedMT, $this->logged_user );

        $this->result['data']['id'] = $result->id;

    }

    /**
     * This method deletes an engine from a user's keyring
     */
    private function disable(){

        if ( empty( $this->id ) ) {
            $this->result[ 'errors' ][ ] = array( 'code' => -5, 'message' => "Engine id required" );
            return;
        }

        $engineToBeDeleted = EnginesModel_EngineStruct::getStruct();
        $engineToBeDeleted->id = $this->id;
        $engineToBeDeleted->uid = $this->uid;

        $engineDAO = new EnginesModel_EngineDAO( Database::obtain() );
        $result = $engineDAO->disable( $engineToBeDeleted );

        if(! $result instanceof EnginesModel_EngineStruct){
            $this->result[ 'errors' ][ ] = array( 'code' => -9, 'message' => "Deletion failed. Generic error" );
            return;
        }

        $this->result['data']['id'] = $result->id;

    }

    /**
     * This method creates a temporary engine and executes one of it's methods
     */
    private function execute() {

        $tempEngine = null;
        $validEngine = true;

        switch ( strtolower( $this->provider ) ) {
            case strtolower( Constants_Engines::LETSMT ):

                /**
                 * Create a record of type LetsMT
                 */
                $tempEngineRecord = EnginesModel_LetsMTStruct::getStruct();

                $tempEngineRecord->name                                = $this->name;
                $tempEngineRecord->uid                                 = $this->uid;
                $tempEngineRecord->type                                = Constants_Engines::MT;
                $tempEngineRecord->extra_parameters[ 'client_id' ]     = $this->engineData['client_id'];
                $tempEngineRecord->extra_parameters[ 'system_id' ]     = $this->engineData[ 'system_id' ];
                //$tempEngineRecord->extra_parameters[ 'terms_id' ]      = $this->engineData[ 'terms_id' ];

                break;
            default:
                $validEngine = false;
        }

        if( !$validEngine ){
            $this->result[ 'errors' ][ ] = array( 'code' => -4, 'message' => "Engine not allowed" );
            return;
        }

        $tempEngine = Engine::createTempInstance($tempEngineRecord);
        if(! $tempEngine instanceof Engines_AbstractEngine){
            $this->result[ 'errors' ][ ] = array( 'code' => -12, 'message' => "Creating engine failed. Generic error" );
            return;
        }
        $functionParams = $this->engineData['functionParams'];

        $function = $this->engineData[ 'function' ];
        if(empty($function)){
            $this->result[ 'errors' ][ ] = array( 'code' => -10, 'message' => "No function specified" );
            return;
        } elseif (empty(self::$allowed_execute_functions[strtolower($this->provider)])
                || !in_array($function, self::$allowed_execute_functions[strtolower($this->provider)])){
            $this->result[ 'errors' ][ ] = array( 'code' => -11, 'message' => "Function not allowed" );
            return;
        }

        $executeResult = $tempEngine->$function($functionParams);
        if ( isset( $executeResult['error']['code'] ) ) {
                $this->result[ 'errors' ][ ] = $executeResult['error'];
                return;
        }
        $this->result['data']['result'] = $executeResult;
    }

}
