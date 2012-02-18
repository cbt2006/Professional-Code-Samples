<?php

class SearchController extends Controller
{
	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
		$sess=Yii::app()->session;
		$facebook = Yii :: app()->params['facebook'];
		$signedRequest=$facebook->getSignedRequest();
    	$myuid = $facebook->getUser();
		$q = isset($_GET['q']) ? $_GET['q'] : '';
		$svc = isset($_GET['svc']) ? $_GET['svc'] : '';
		if($svc!='') $sess['svc']=$svc;
    		  	// renders the view file 'protected/views/site/index.php'
		// using the default layout 'protected/views/layouts/main.php'
		$this->actionLogin();
	    //echo $sess['svc'];
		$this->render('index',array(
			'myuid'=> $myuid,
			'facebook'=> $facebook,
			'signedRequest'=> $signedRequest,
			'svc' => $sess['svc'],
			'q' => $q,
		));
	}
	
	public function actionLogin()
	{
		$sess=Yii::app()->session;
		$sess['user'] = 'gregt';
		$sess['pw'] = 'gregt';
		//$sess['svc'] = isset($_GET['svc']) ? $_GET['svc'] : 'Symphony';
		switch($sess['svc']) {
			case 'Symphony':
				$this->loginSymphony();
				break;
			case 'Horizon':
				$this->loginHorizon();
				break;
			case 'Discovery':
				$this->loginDiscovery();
				break;
			case 'Amazon':
				//$this->loginAmazon();
				//break;
			default:
				//echo Yii::t("sd", "Web client not recognized");
		}
		//$this->render('index');
	}

	public function loginSymphony()
	{
		$sess=Yii::app()->session;
		// authorize user (generate session ID) given username and password
		// try user=gregt and pw=gregt
		$auth = $this->sdGet("ws", $sess['symUrl']."rest/security/loginUser?clientID=".Yii::app()->params['symClientId']."&login=".$sess['user']."&password=".$sess['pw']);
		$sessID = $auth->sessionToken;
		$sess['sessID'] = (string) $sessID;
		//echo $sess['sessID'];
		//$userInfo = simplexml_load_file($sess['symUrl']."rest/security/lookupUserInfo?clientID=".Yii::app()->params['symClientId']."&userID=SIRSI&sessionToken=".$sessID);
		//var_dump($userInfo);
	}
	
	public function loginHorizon()
	{
		$sess=Yii::app()->session;
		// loginUser returns session ID
		// try user = 21267005833827 and pw = 4333
		$login = $this->sdGet("ws", $sess['hzUrl']."rest/standard/loginUser?clientID=".Yii::app()->params['hzClientId']."&login=****&password=****");
		//var_dump($login);
		//var_dump($http_response_header);
		$sessID = $login->sessionToken;
		$sess['sessID'] = (string) $sessID;
		//echo $sess['sessID'];
	}

	public function loginDiscovery() 
	{
		$sess=Yii::app()->session;
		// authenticate user
		// Create wsdl-based soap client for the specified service (security)
		$soapClient = new SoapClient("http://****/services/****?wsdl");
		// The SD web service requires the clientID be passed in the soap header
		$opt->clientID=Yii::app()->params['disClientId'];
		// Create the soap header
		$header = new SoapHeader('http://****/', '****', $opt, false);
		$soapClient->__setSoapHeaders(array($header));
		// Show the list of methods available in the specified service
		//var_dump($soapClient->__getFunctions());
		// Create of attributes needed by the method
		$options=array(
		    'profileCode'=>'****',
		    'userName'=>'****',
		    'password'=>'****'
		    );
		    $sessID = $soapClient->authenticateUser($options);
		    $sess['sessID'] = $sessID->return;
		    //echo $sess['sessID'];
	}
	
	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionSearchresults()
	{
		$facebook = Yii :: app()->params['facebook'];
		$signedRequest=$facebook->getSignedRequest();
	    $myuid = $facebook->getUser();
	    $sess=Yii::app()->session;
	    if(!empty($_GET['library_id']) && strtolower($_GET['library_id']) == 'amazon') {
		    $sess['svc'] = 'Amazon';
	    }
		switch($sess['svc']) {
			case 'Symphony':
				$results = $this->searchSymphony();
				break;
			case 'Horizon':
				$results = $this->searchHorizon();
				break;
			case 'Discovery':
				$results = $this->searchDiscovery();
				break;
			case 'Amazon':
				$results = $this->searchAmazon();
				//var_dump($results[0]);
				//exit;
				break;
			default:
				$results[0] = Yii::t("sd", "Web client not recognized");
				//$results = null;
		}
	  	// renders the view file 'protected/views/site/index.php'
		// using the default layout 'protected/views/layouts/main.php'
		$this->render('searchresults',array(
			'myuid'=> $myuid,
			'facebook'=> $facebook,
			'signedRequest'=> $signedRequest,
			'results'=>$results
		));
	}
	
	// TODO: Pull out only values needed instead of loop
	function searchSymphony() 
	{
		$sess=Yii::app()->session;
		// perform search query
		if(!empty($_GET['basicSearchButton']) && $_GET['basicSearchButton'] != '' && !empty($_GET['q']) && $_GET['q'] != '') {
			if(strtolower($_GET['searchType']) == 'keyword') {
				$qResults = $this->sdGet("ws", $sess['symUrl']."rest/standard/searchCatalog?clientID=".Yii::app()->params['symClientId']."&sessionToken=".$sess['sessID']."&query=&term1=".urlencode($_GET['q']), $sess['sessID']);
			} elseif(strtolower($_GET['searchType']) == 'title') {
				$qResults = $this->sdGet("ws", $sess['symUrl']."rest/standard/searchCatalog?clientID=".Yii::app()->params['symClientId']."&sessionToken=".$sess['sessID']."&query=&searchType1=TITLE&term1=".urlencode($_GET['q']), $sess['sessID']);
			} elseif(strtolower($_GET['searchType']) == 'author') {
				$qResults = $this->sdGet("ws", $sess['symUrl']."rest/standard/searchCatalog?clientID=".Yii::app()->params['symClientId']."&sessionToken=".$sess['sessID']."&query=&searchType1=AUTHOR&term1=".urlencode($_GET['q']), $sess['sessID']);
			} elseif(strtolower($_GET['searchType']) == 'subject') {
				$qResults = $this->sdGet("ws", $sess['symUrl']."rest/standard/searchCatalog?clientID=".Yii::app()->params['symClientId']."&sessionToken=".$sess['sessID']."&query=&searchType1=SUBJECT&term1=".urlencode($_GET['q']), $sess['sessID']);
			}
			
		// advanced search - check if the advanced search form's box was clicked
		} elseif(!empty($_GET['advSearchButton']) && $_GET['advSearchButton'] != '') {
			$tmCt = 1; // counter for each term - has to increment for each term
			$opCt = 1; // counter for operators like NOT - must increment each time used
			$stCt = 1; // search type counter
			$qString = $sess['symUrl']."rest/standard/searchCatalog?clientID=".Yii::app()->params['symClientId']."&sessionToken=".$sess['sessID']. '&query=' . '&';
			// individual search terms/words
			if(!empty($_GET['allWordsField']) && $_GET['allWordsField'] != '') {
				$qString .= 'term'.$tmCt.'='.urlencode($_GET['allWordsField']).'&';
				$tmCt++;
			}
			// exact terms/phrase
			if(!empty($_GET['exactPhraseField']) && $_GET['exactPhraseField'] != '') {
				$qString .= 'term'.$tmCt.'=' . urlencode($_GET['exactPhraseField']) . '&exactMatch=true' . '&';
				$tmCt++;
			}
			// exclude these terms
			if(!empty($_GET['unwantedTermsField']) && $_GET['unwantedTermsField'] != '') {
				$qString .= 'operator'.$opCt.'=NOT&term'.$tmCt.'='.urlencode($_GET['unwantedTermsField']).'&';
				$tmCt++;
				$opCt++;
			}
			// limit by format
			if(!empty($_GET['limitFormat']) && $_GET['limitFormat'] != '') {
				$qString .= 'itemtypeFilter=' . '&';
			}
			// limit by language
			if(!empty($_GET['language']) && $_GET['language'] != '') {
				$qString .= 'languageFilter=' . '&';
			}
			// limit by library
			if(!empty($_GET['libraryID']) && $_GET['libraryID'] != '') {
				$qString .= 'libraryFilter=' . '&';
			}
			// find/exclude title
			if(!empty($_GET['title']) && $_GET['title'] != '' && !empty($_GET['searchTitle']) && $_GET['searchTitle'] != '') {
				if($_GET['searchTitle'] == 'include') {
					$qString .= 'searchType'.$stCt.'=TITLE&term'.$tmCt.'='.urlencode($_GET['title']).'&';
				} elseif($_GET['searchTitle'] == 'exclude') {
					//echo 'hldjflsk';
					$qString .= 'operator'.$opCt.'=NOT&searchType'.$stCt.'=TITLE&term'.$tmCt.'='.urlencode($_GET['title']).'&';
					$opCt++;
				}
				$stCt++;
				$tmCt++;
			}
			// find/exclude author
			if(!empty($_GET['author']) && $_GET['author'] != '' && !empty($_GET['searchAuthor']) && $_GET['searchAuthor'] != '') {
				if($_GET['searchAuthor'] == 'include') {
					$qString .= 'searchType'.$stCt.'=AUTHOR&term'.$tmCt.'='.urlencode($_GET['author']).'&';
				} elseif($_GET['searchAuthor'] == 'exclude') {
					$qString .= 'operator'.$opCt.'=NOT&searchType'.$stCt.'=AUTHOR&term'.$tmCt.'='.urlencode($_GET['author']).'&';
					$opCt++;
				}
				$stCt++;
				$tmCt++;
			}
			// find/exclude subject
			if(!empty($_GET['subject']) && $_GET['subject'] != '' && !empty($_GET['searchSubject']) && $_GET['searchSubject'] != '') {
				if($_GET['searchSubject'] == 'include') {
					$qString .= 'searchType'.$stCt.'=SUBJECT&term'.$tmCt.'='.urlencode($_GET['subject']).'&';
				} elseif($_GET['searchSubject'] == 'exclude') {
					$qString .= 'operator'.$opCt.'=NOT&searchType'.$stCt.'=SUBJECT&term'.$tmCt.'='.urlencode($_GET['subject']).'&';
					$opCt++;
				}
				$stCt++;
				$tmCt++;
			}			
			$qResults = $this->sdGet("ws", $qString, $sess['sessID']);
			echo $qString;
		}
		
		if(isset($qResults->HitlistTitleInfo) && count($qResults->HitlistTitleInfo) > 0) {
			// copy all rows into an array - allows generic field checking in view - no need to parse anything client-specific
			$ret = array();
			foreach($qResults->HitlistTitleInfo as $row) {
				$last = count($ret);
				$ret[$last] = array();
				if(array_key_exists('title', $row)) { $ret[$last]['title'] = (string) $row->title; }
				if(array_key_exists('author', $row)) { $ret[$last]['author'] = (string) $row->author; }
				if(array_key_exists('ISBN', $row)) { $ret[$last]['ISBN'] = (string) $row->ISBN; }
				if(array_key_exists('materialType', $row)) { $ret[$last]['materialType'] = (string) $row->materialType; }
				if(array_key_exists('titleID', $row)) { $ret[$last]['titleID'] = (string) $row->titleID; }
			}
			return $ret;
		} else {
			// if no records are found, return a message to indicate this. using array syntax since that's what's expected in view
			$qResults[0] = "Sorry, no results found for your query.";
			return $qResults;
		}
	}
	
	// TODO: Pull out only values needed instead of loop
	public function searchHorizon()
	{
		$sess=Yii::app()->session;
		// perform catalog search
		if(!empty($_GET['basicSearchButton']) && $_GET['basicSearchButton'] != '' && !empty($_GET['q']) && $_GET['q'] != '') {
			if(strtolower($_GET['searchType']) == 'keyword') {
				$results = $this->sdGet("ws", $sess['hzUrl']."rest/standard/searchCatalog?term=".urlencode($_GET['q'])."&clientID=".Yii::app()->params['symClientId']."", $sess['sessID']);
			} elseif(strtolower($_GET['searchType']) == 'title') {
				$results = $this->sdGet("ws", $sess['hzUrl']."rest/standard/searchCatalog?term=".urlencode($_GET['q'])."&indexID=TW&clientID=".Yii::app()->params['symClientId']."", $sess['sessID']);
			} elseif(strtolower($_GET['searchType']) == 'author') {
				$results = $this->sdGet("ws", $sess['hzUrl']."rest/standard/searchCatalog?term=".urlencode($_GET['q'])."&indexID=AW&clientID=".Yii::app()->params['symClientId']."", $sess['sessID']);
			} elseif(strtolower($_GET['searchType']) == 'subject') {
				$results = $this->sdGet("ws", $sess['hzUrl']."rest/standard/searchCatalog?term=".urlencode($_GET['q'])."&indexID=SW&clientID=".Yii::app()->params['symClientId']."", $sess['sessID']);
			}
		// advanced search - check if the advanced search form's box was clicked
		} elseif(!empty($_GET['advSearchButton']) && $_GET['advSearchButton'] != '') {
			$url = $sess['hzUrl']."rest/standard/searchCatalog?clientID=".Yii::app()->params['symClientId']."&";
			$url .= ($_GET['allWordsField'] == '') ? '' : ('term=' . urlencode($_GET['allWordsField'])) . '&';
			$url .= ($_GET['exactPhraseField'] == '') ? '' : '';
			$url .= ($_GET['unwantedTermsField'] == '') ? '' : ('term=-' . urlencode($_GET['unwantedTermsField'])) . '&';
			// limit format
			$url .= ($_GET['limitBy'] == '') ? '' : '' . 'qf=FORMAT%3A' . '&';
			// limit by language
			$url .= ($_GET['language'] == '') ? '' : '';
			// limit by library
			$url .= ($_GET['libraryID'] == '') ? '' : '';
			
			if(!empty($_GET['title']) && $_GET['title'] != '' && !empty($_GET['searchTitle']) && $_GET['searchTitle'] != '') {
				if($_GET['searchTitle'] == 'include') {
					$url .= 'term=' . urlencode($_GET['title']) . '&indexID=TW' . '&';
				} elseif($_GET['searchTitle'] == 'exclude') {
					$url .= 'term=-' . urlencode($_GET['title']) . '&indexID=TW' . '&';
				}
			}
			if(!empty($_GET['author']) && $_GET['author'] != '' && !empty($_GET['searchAuthor']) && $_GET['searchAuthor'] != '') {
				if($_GET['searchAuthor'] == 'include') {
					$url .= 'term=' . urlencode($_GET['author']) . '&indexID=AW' . '&';
				} elseif($_GET['searchAuthor'] == 'exclude') {
					$url .= 'term=-' . urlencode($_GET['author']) . '&indexID=AW' . '&';
				}
			}
			if(!empty($_GET['subject']) && $_GET['subject'] != '' && !empty($_GET['searchSubject']) && $_GET['searchSubject'] != '') {
				if($_GET['searchSubject'] == 'include') {
					$url .= 'term=' . urlencode($_GET['subject']) . '&indexID=SW' . '&';
				} elseif($_GET['searchSubject'] == 'exclude') {
					$url .= 'term=-' . urlencode($_GET['subject']) . '&indexID=SW' . '&';
				}
			}			
			$results = $this->sdGet("ws", $url, $sess['sessID']);
		} else {
			echo "Invalid search, please try again.";
			$results = null;
		}
		
		if(isset($results->titleInfo) && count($results->titleInfo) > 0) {
			$ret = array();
			foreach($results->titleInfo as $row) {
				$last = count($ret);
				$ret[$last] = array();
				foreach($row->children() as $child) {
					$ret[$last][$child->getName()] = (string) $child;
				}
			}
			return $ret;
		} else {
			$results[0] = (Yii::t("sd", "Sorry, no results found for your query."));
			return $results;
		}
	}

	public function searchDiscovery()
	{
		$sess=Yii::app()->session;
		// search catalog - use openSearch, which is not related to daphne web service calls
		
		// basic search - check if the basic search form's box was clicked
		if(!empty($_GET['basicSearchButton']) && $_GET['basicSearchButton'] != '' && !empty($_GET['q']) && $_GET['q'] != '') {
			if(strtolower($_GET['searchType']) == 'keyword') {
				$searchCatalog = $this->sdGet("ws", $sess['osUrl']."os?pr=****&ext=dss&q=".urlencode($_GET['q']), $sess['sessID']);
			} elseif(strtolower($_GET['searchType']) == 'title') {
				$searchCatalog = $this->sdGet("ws", $sess['osUrl']."os?pr=****&ext=dss&q=TITLE%3A".urlencode($_GET['q']), $sess['sessID']);
			} elseif(strtolower($_GET['searchType']) == 'author') {
				$searchCatalog = $this->sdGet("ws", $sess['osUrl']."os?pr=****&ext=dss&q=AUTHOR%3A".urlencode($_GET['q']), $sess['sessID']);
			} elseif(strtolower($_GET['searchType']) == 'subject') {
				$searchCatalog = $this->sdGet("ws", $sess['osUrl']."os?pr=****&ext=dss&q=SUBJECT%3A".urlencode($_GET['q']), $sess['sessID']);
			}
			//var_dump($searchCatalog);
		// advanced search - check if the advanced search form's box was clicked
		} elseif(!empty($_GET['advSearchButton']) && $_GET['advSearchButton'] != '') {
			$url = $sess['osUrl']."os?pr=DEFAULT&ext=dss&";
			$url .= (empty($_GET['allWordsField']) || $_GET['allWordsField'] == '') ? '' : ('q=' . urlencode($_GET['allWordsField']) . '&');
			$url .= (empty($_GET['exactPhraseField']) || $_GET['exactPhraseField'] == '') ? '' : ('q=' . urlencode($_GET['exactPhraseField']) . '&'); // don't know if this exists for Discovery, so leaving w/basic search
			$url .= (empty($_GET['unwantedTermsField']) || $_GET['unwantedTermsField'] == '') ? '' : ('q=-' . urlencode($_GET['unwantedTermsField']) . '&');
			$url .= (empty($_GET['limitBy']) || $_GET['limitBy'] == '') ? '' : ('q=FORMAT%3A' . urlencode($_GET['limitBy']) . '&');
			$url .= (empty($_GET['language']) || $_GET['language'] == '') ? '' : ('ln=' . '&'); // use standard language abbreviations - e.g., en_US
			$url .= (empty($_GET['libraryID']) || $_GET['libraryID'] == '') ? '' : '';
			if(!empty($_GET['title']) && $_GET['title'] != '' && !empty($_GET['searchTitle']) && $_GET['searchTitle'] != '') {
				if($_GET['searchTitle'] == 'include') {
					$url .= 'q=TITLE%3A' . urlencode($_GET['title']) . '&';
				} elseif($_GET['searchTitle'] == 'exclude') {
					$url .= 'q=-TITLE%3A' . urlencode($_GET['title']) . '&';
				}
			}
			if(!empty($_GET['author']) && $_GET['author'] != '' && !empty($_GET['searchAuthor']) && $_GET['searchAuthor'] != '') {
				if($_GET['searchAuthor'] == 'include') {
					$url .= 'q=AUTHOR%3A' . urlencode($_GET['author']) . '&';
				} elseif($_GET['searchAuthor'] == 'exclude') {
					$url .= 'q=-AUTHOR%3A' . urlencode($_GET['author']) . '&';
				}
			}
			if(!empty($_GET['subject']) && $_GET['subject'] != '' && !empty($_GET['searchSubject']) && $_GET['searchSubject'] != '') {
				if($_GET['searchSubject'] == 'include') {
					$url .= 'q=SUBJECT%3A' . urlencode($_GET['subject']) . '&';
				} elseif($_GET['searchSubject'] == 'exclude') {
					$url .= 'q=-SUBJECT%3A' . urlencode($_GET['subject']) . '&';
				}
			}
			echo $url;
			$searchCatalog = $this->sdGet("ws", $url, $sess['sessID']);
		}
		$ns = $searchCatalog->entry->getNameSpaces(true);
		$dss = $searchCatalog->entry->children($ns['dss']);
		
		if(isset($searchCatalog->entry) && count($searchCatalog->entry) > 0) {
			$ret = array();
			foreach($searchCatalog->entry as $entry) {
				$dss = $entry->children($ns['dss']);
				$last = count($ret);
				$ret[$last] = array();
				if(count($dss->field) > 0) {
					foreach($dss->field as $field) {
						$name = (string) $field->attributes()->name;
						if($name != '') $ret[$last][$name] = (string) $field;
					}
				}
			}
			return $ret;
		} else {
			$searchCatalog[0] = (Yii::t("sd", "Sorry, no results found for your query."));
			return $searchCatalog;
		}
	}
	
	// search Amazon catalog
	public function searchAmazon()
	{
		$sess=Yii::app()->session;
		include("amazon_api_class.php");
		// only use basic search functionality for Amazon for now
		if(!empty($_GET['basicSearchButton']) && $_GET['basicSearchButton'] != '' && !empty($_GET['q']) && $_GET['q'] != '') {
			$obj = new AmazonProductAPI();
			try {
				$results = $obj->searchProducts(urlencode($_GET['q']), AmazonProductAPI::BOOKS, "TITLE");
			} catch (Exception $e) {
				echo $e->getMessage();
			}
		}
		
		if(isset($results->Items->Item) && count($results->Items->Item) > 0) {
			$ret = array();
			foreach($results->Items->Item as $item) {
				$last = count($ret);
				$ret[$last] = array();
				if(array_key_exists('ASIN', $item)) { $ret[$last]['ASIN'] = (string) $item->ASIN; }
				if(array_key_exists('DetailPageURL', $item)) { $ret[$last]['DetailPageURL'] = (string) $item->DetailPageURL; }
				if(isset($item->ItemAttributes) && count($item->ItemAttributes) > 0) {
					foreach($item->ItemAttributes as $attr) {
						if(array_key_exists('Title', $attr)) { $ret[$last]['Title'] = (string) $attr->Title; }
						if(array_key_exists('Author', $attr)) { $ret[$last]['Author']= (string) $attr->Author; }
						if(array_key_exists('ISBN', $attr)) { $ret[$last]['ISBN'] = (string) $attr->ISBN; }
						if(array_key_exists('ProductGroup', $attr)) { $ret[$last]['ProductGroup'] = (string) $attr->ProductGroup; }
						if(array_key_exists('titleID', $attr)) { $ret[$last]['titleID'] = (string) $attr->titleID; }
						//if(array_key_exists('titleID', $attr)) { $ret[$last]['titleID'] = (string) $attr->titleID; }
					}
				}
			}
			return $ret;
		} else {
			$results[0] = (Yii::t("sd", "Sorry, no results found for your query."));
			return $results;
		}
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionItemdetails()
	{
		$facebook = Yii :: app()->params['facebook'];
		$signedRequest=$facebook->getSignedRequest();
    	$myuid = $facebook->getUser();
    	$sess=Yii::app()->session;
	    $this->actionLogin();
	  	// renders the view file 'protected/views/site/index.php'
		// using the default layout 'protected/views/layouts/main.php'
		switch($sess['svc']) {
			case 'Symphony':
				$details = $this->viewDetailsSymphony();
				break;
			case 'Horizon':
				$details = $this->viewDetailsHorizon();
				break;
			case 'Discovery':
				$details = $this->viewDetailsDiscovery();
				break;
			case 'Amazon':
				$details = $this->viewDetailsAmazon();
				break;
			default:
				echo "Details view not available for this client.";
				$details = null;
		}
		$this->render('itemdetails',array(
			'myuid'=> $myuid,
			'facebook'=> $facebook,
			'signedRequest'=> $signedRequest,
			'details'=>$details
		));
	}
	
	public function viewDetailsSymphony()
	{
		$sess=Yii::app()->session;
		// get item/title details
		$details = $this->sdGet("ws", $sess['symUrl']."rest/standard/lookupTitleInfo?clientID=".Yii::app()->params['symClientId']."&sessionToken=".$sess['sessID']."&titleID=".$_GET['titleID']."&includeAvailabilityInfo=true&includeItemInfo=true&includeCatalogingInfo=true&includeOrderInfo=true&includeOPACInfo=true&includeBoundTogether=true&includeMarcHoldings=true&includeRelatedSearchInfo=true", $sess['sessID']);
		if(isset($details->TitleInfo) && count($details->TitleInfo) > 0) {
			$ret = array();
			foreach($details->TitleInfo as $title) {
				$last = count($ret);
				$ret[$last] = array();
				if(array_key_exists('title', $title)) { $ret[$last]['title'] = (string) $title->title; }
				if(array_key_exists('author', $title)) { $ret[$last]['author'] = (string) $title->author; }
				if(array_key_exists('ISBN', $title)) { $ret[$last]['ISBN'] = (string) $title->ISBN; }
				if(array_key_exists('materialType', $title)) { $ret[$last]['materialType'] = (string) $title->materialType; }
				if(array_key_exists('datePublished', $title)) { $ret[$last]['datePublished'] = (string) $title->datePublished; }
				if(array_key_exists('publisherName', $title)) { $ret[$last]['publisherName'] = (string) $title->publisherName; }
				if(array_key_exists('TitleAvailabilityInfo', $title)) { 
					$ret[$last]['TitleAvailabilityInfo'] = array();
					foreach($title->TitleAvailabilityInfo as $info) {
						if(array_key_exists('totalCopiesAvailable', $info)) { $ret[$last]['TitleAvailabilityInfo']['totalCopiesAvailable'] = $info->totalCopiesAvailable; }
						if(array_key_exists('libraryWithAvailableCopies', $info)) {
							for($i = 0; $i < count($info->libraryWithAvailableCopies); $i++) {
								$ret[$last]['TitleAvailabilityInfo']['libraryWithAvailableCopies'][] = $info->libraryWithAvailableCopies;
							}
						}
					}
				}
			}
			return $ret;
		} else {
			$details[0] = (Yii::t("sd", "Sorry, no results found for your query."));
			return $details;
		}
	}
	
	public function viewDetailsHorizon()
	{
		$sess=Yii::app()->session;
		// retrieve item details
		$details = $this->sdGet("ws", $sess['hzUrl']."rest/standard/lookupTitleInfo?clientID=".Yii::app()->params['symClientId']."&titleKey=".$_GET['titleID']."&includeItemInfo=true", $sess['sessID']);
		if(isset($details->titleInfo) && count($details->titleInfo) > 0) {
			$ret = array();
			foreach($details->titleInfo as $title) {
				$last = count($ret);
				$ret[$last] = array();
				if(array_key_exists('title', $title)) { $ret[$last]['title'] = (string) $title->title; }
				if(array_key_exists('author', $title)) { $ret[$last]['author'] = (string) $title->author; }
				if(array_key_exists('titleKey', $title)) { $ret[$last]['titleKey'] = (string) $title->titleKey; }
				if(array_key_exists('materialType', $title)) { $ret[$last]['materialType'] = (string) $title->materialType; }
				if(array_key_exists('publisher', $title)) { $ret[$last]['publisher'] = (string) $title->publisher; }
				if(array_key_exists('pubDate', $title)) { $ret[$last]['pubDate'] = (string) $title->pubDate; }
				if(array_key_exists('available', $title)) { $ret[$last]['available'] = (string) $title->available; }
				if(isset($title->itemInfo) && count($title->itemInfo) > 0) {
					$ret[$last]['itemInfo'] = array();
					foreach($title->itemInfo as $item) {
						$last2 = count($ret[$last]['itemInfo']);
						if(array_key_exists('locationDescription', $item)) { $ret[$last]['itemInfo'][$last]['locationDescription'] = (string) $item->locationDescription; }
						if(array_key_exists('materialType', $item)) { $ret[$last]['itemInfo'][$last]['materialType'] = (string) $item->materialType; }
						if(array_key_exists('itemKey', $item)) { $ret[$last]['itemInfo'][$last]['itemKey'] = (string) $item->itemKey; }
						if(array_key_exists('callNumber', $item)) { $ret[$last]['itemInfo'][$last]['callNumber'] = (string) $item->callNumber; }
						if(array_key_exists('statusDescription', $item)) { $ret[$last]['itemInfo'][$last]['statusDescription'] = (string) $item->title; }
					}
				}
			}
		}
		return $details;
	}
	
	public function viewDetailsDiscovery()
	{
		// not working at this time
		$notAvail[0] = "Discovery functionality currently unavailable.";
		return $notAvail;
	}
	
	public function viewDetailsAmazon()
	{
		// not working at this time
		$notAvail[0] = "Amazon functionality currently unavailable.";
		return $notAvail;
	}
}