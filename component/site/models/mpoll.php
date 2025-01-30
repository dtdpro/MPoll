<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Date\Date;

// Load Extra Pakcages
require JPATH_COMPONENT."/vendor/autoload.php";
require JPATH_COMPONENT."/lib/paypal.php";

use MReCaptcha\MReCaptcha;

class MPollModelMPoll extends JModelLegacy
{
	var $errmsg = "";

    var $hasFilters = false;

	function getPoll($pollid)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__mpoll_polls');
		$query->where('poll_id = '.$db->escape($pollid));
		$query->where('published > 0');
		$db->setQuery( $query );
		$pdata = $db->loadObject();

		if ($pdata) {
			if ( property_exists( $pdata, 'poll_results_emails' ) && $pdata->poll_results_emails !== null ) {
				$registry = new Registry;
				$registry->loadString( $pdata->poll_results_emails );
				$pdata->poll_results_emails = $registry->toArray();
			}
		}

        // Set searchable results message default
        $pdata->resultsMsg = "";

		return $pdata;
	}

	function getQuestions($pollid,$options=false,$count=false,$filterQuestions=false)
	{
		$db = JFactory::getDBO();
		$app=Jfactory::getApplication();
		$jinput = JFactory::getApplication()->input;
		$query=$db->getQuery(true);
		$query->select('*');
		$query->from('#__mpoll_questions');
		$query->where('published > 0');
		$query->where('q_poll = '.$db->escape($pollid));
        if ($filterQuestions) $query->where('q_filter = 1 ');
        if (!$filterQuestions) $query->where('q_hidden = 0 ');
		$query->order('ordering ASC');
		$db->setQuery( $query );
		$qdata = $db->loadObjectList();
		foreach ($qdata as &$q) {

			//Load option and count if needed
			if ($options) {
				//Load options
				if ($q->q_type == "multi" || $q->q_type == "mcbox" || $q->q_type == "dropdown" || $q->q_type == "mlist") {
					$qo=$db->getQuery(true);
					$qo->select('opt_txt as text, opt_id as value, opt_disabled, opt_correct, opt_color, opt_other, opt_selectable, opt_blank');
					$qo->from('#__mpoll_questions_opts');
					$qo->where('opt_qid = '.$q->q_id);
					$qo->where('published > 0');
					$qo->order('ordering ASC');
					$db->setQuery($qo);
					$q->options = $db->loadObjectList();

					//Load counts
					if ($count) {
						foreach ($q->options as &$o) {
							$qa = $db->getQuery(true);
							$qa->select('count(*)');
							$qa->from('#__mpoll_results');
							$qa->where('res_qid = '.$q->q_id);
							$qa->where('res_ans LIKE "%'.$o->value.'%"');
							$qa->group('res_qid');
							$db->setQuery($qa);
							$o->anscount = (int)$db->loadResult();
							if (isset($q->anscount)) $q->anscount = $q->anscount + $o->anscount;
							else $q->anscount = $o->anscount;
						}
					}
				}
			}

			//Load Question Params
			$registry = new JRegistry();
			$registry->loadString($q->params);
			$q->params = $registry->toObject();

			//Set default/saved values
			$fn='q_'.$q->q_id;
			$value = $app->getUserState('mpoll.poll'.$pollid.'.'.$fn,null);
			if (!$value) {
				$value = $jinput->getVar($fn,$q->q_default);
			}
			$other = $app->getUserState('mpoll.poll'.$pollid.'.'.$fn.'_other',$q->q_default);
			if ($q->q_type == 'mlimit' || $q->q_type == 'multi' || $q->q_type == 'dropdown' || $q->q_type == 'mcbox' || $q->q_type == 'mlist') {
				$q->value=explode(" ",$value);
				$q->other = $other;
			} else if ($q->q_type == 'cbox' || $q->q_type == 'yesno') {
				$q->value=$value;
			} else if ($q->q_type == 'datedropdown') {
				$q->value=$value;
			} else {
				$q->value=$value;
			}
		}
		return $qdata;
	}

	public function save($pollid)
	{
		$this->checkToken() or die(Text::_('JINVALID_TOKEN'));

		// Initialise variables
		$cfg = MPollHelper::getConfig();
		$jinput = JFactory::getApplication()->input;

		// Honeypot
		if ($cfg->usehoneypot) {
			$honeyPot = $jinput->getVar( 'name', "" );
			if ( $honeyPot ) {
				die( Text::_( 'JINVALID_TOKEN' ) );
			}
		}

		$data = $jinput->getVar('jform', array(), 'post', 'array');
		$isNew = true;
		$db = $this->getDbo();
		$app = Jfactory::getApplication();
		$session = JFactory::getSession();
		$user = JFactory::getUser();
		$date = new JDate('now');
		$pollinfo = $this->getPoll($pollid);
		$jconfig = JFactory::getConfig();
		// Include the content plugins for the on save events.
		JPluginHelper::importPlugin('content');

		if ($pollinfo->poll_regreq == 1 && $user->id == 0) {
			$this->setError('Login required.  Please resubmit.');
			return false;
		} else {
			if ( !in_array($pollinfo->access,$user->getAuthorisedViewLevels())) {
				$this->setError('Access denied');
				return false;
			}
		}

		// Allow an exception to be thrown.
		try
		{
			// Setup item and bind data
			$fids = array();
			$optfs = array();
			$moptfs = array();
			$upfile=array();
			$item = new stdClass();
			$other = new stdClass();
            $otherAlt = new stdClass();
			$flist = $this->getQuestions($pollid,false);
			foreach ($flist as $d) {
				$fieldname = 'q_'.$d->q_id;
				if ($d->q_type == 'attach') {
					$upfile[]=$fieldname;
				} else if ($d->q_type=="mcbox" || $d->q_type=="mlist") {
					$item->$fieldname = implode(" ",$data[$fieldname]);
				} else if ($d->q_type=='cbox') {
					$item->$fieldname = ($data[$fieldname]=='on') ? "1" : "0";
				} else if ($d->q_type=='datedropdown') {
					$fmonth = (int)$data[$fieldname.'_month'];
					$fday = (int)$data[$fieldname.'_day'];
					$fyear = (int)$data[$fieldname.'_year'];
					if ($fmonth < 10) $fmonth = "0".$fmonth;
					if ($fday < 10) $fday = "0".$fday;
					$item->$fieldname = $fmonth.'-'.$fday.'-'.$fyear;
				} else if ($d->q_type=='gmap') {
                    $address = $data[$fieldname];
                    $item->$fieldname = $address;
                    if ($cfg->gmaps_geocode_key) {
                        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address) . '&key=' . $cfg->gmaps_geocode_key;
                        $gdata_json = $this->curl_file_get_contents($url);
                        $gdata = json_decode($gdata_json);
                        $other->$fieldname = $gdata->results[0]->geometry->location->lat; //latitude
                        $otherAlt->$fieldname = $gdata->results[0]->geometry->location->lng; //longitude
                    }
                } else {
					$item->$fieldname = $data[$fieldname];
				}

				if ($d->q_type=="multi" || $d->q_type=="dropdown") {
					$optfs[]=$fieldname;
					$other->$fieldname=$data[$fieldname."_other"];
				}
				if ($d->q_type=="mcbox" || $d->q_type=="mlist") {
					$moptfs[]=$fieldname;
					$other->$fieldname=$data[$fieldname."_other"];
				}
				$fids[]=$d->uf_id;
				if ($d->cf_type != 'password') {
					$app->setUserState('mpoll.poll'.$pollid.'.'.$fieldname, $item->$fieldname);
					if ($other->$fieldname) {
						$app->setUserState('mpoll.poll'.$pollid.'.'.$fieldname.'_other', $other->$fieldname);
					}
				}
			}

			// reCAPTCHA
			if ($pollinfo->poll_recaptcha) {
				$recaptchaCheck = new MReCaptcha($cfg->rc_api_secret);

				if ($cfg->rc_theme == "v3") {
					$resp = $recaptchaCheck->setScoreThreshold($cfg->rc_threshold)
					                  ->setExpectedAction('submit')
					                  ->verify($_POST["g-recaptcha-response"]);
				} else {
					$resp = $recaptchaCheck->verify($_POST["g-recaptcha-response"]);
				}

				if (!$resp->isSuccess()) {
					$this->setError('reCAPTCHA verification unsuccessful.  Please resubmit.');
					return false;
				}
			}

            // Determine pay status
            $paymentNeeded = false;
            $subNeeded = false;
            if ($pollinfo->poll_payment_enabled) {
                if ($pollinfo->poll_payment_trigger != 0) {
                    $trigger = 'q_'.$pollinfo->poll_payment_trigger;
                    if ($item->$trigger == 1) {
                        $paymentNeeded = true;
                    }
                } else {
                    $paymentNeeded = true;
                }
                if ($pollinfo->poll_payment_subplan_trigger != 0) {
                    $trigger = 'q_'.$pollinfo->poll_payment_subplan_trigger;
                    if ($item->$trigger == 1) {
                        $subNeeded = true;
                    }
                }
            }

			// Save completed
			$pubid = bin2hex(random_bytes(16));
			$cmrec=new stdClass();
			$cmrec->cm_user=$user->id;
			$cmrec->cm_poll=$pollid;
			$cmrec->cm_pubid=$pubid;
            if ($subNeeded) {
                $cmrec->cm_type="subscription";
                $cmrec->cm_status="unpaid";
            }
            else if ($paymentNeeded) {
                $cmrec->cm_type="order";
                $cmrec->cm_status="unpaid";
            }
			else {
                $cmrec->cm_type="submission";
                $cmrec->cm_status="completed";
            }
			$cmrec->cm_useragent=$_SERVER['HTTP_USER_AGENT'];
			$cmrec->cm_ipaddr=$this->getIPAddress();
			if (!$db->insertObject('#__mpoll_completed',$cmrec)) {
				$this->setError("Error saving compleition record.  Please resubmit.");
				return false;
			}
			$subid = $db->insertid();


			// Upload files
			foreach ($upfile as $u) {
				$config		= JFactory::getConfig();
				$userfiles = $jinput->files->get($u, array(), 'array');
				$uploaded_files = array();
				foreach ($userfiles as $uf) {
					if (!$uf['error']) {
						// Build the appropriate paths
						$tmp_dest = JPATH_BASE . '/media/com_mpoll/upload/' . $subid . "_" . str_replace( "q_",
								"",
								$u ) . "_" . $uf['name'];
						$tmp_src  = $uf['tmp_name'];

						// Move uploaded file
						jimport( 'joomla.filesystem.file' );
						if ( $this->canUpload( $uf, $err ) ) {
							$uploaded = JFile::upload( $tmp_src, $tmp_dest );
							$uploaded_files[] = '/media/com_mpoll/upload/' . $subid . "_" . str_replace( "q_",
									"",
									$u ) . "_" . $uf['name'];
						} else {
							$this->setError( $err );
							return false;
						}
					}
				}
				if (count($uploaded_files)) $item->$u = implode(",",$uploaded_files);
				else $item->$u = "";
			}

			// Get Options
			$odsql=$db->getQuery(true);
			$odsql->select('*');
			$odsql->from('#__mpoll_questions_opts');
			$db->setQuery($odsql);
			$optionsdata = array();
			$optres = $db->loadObjectList();
			foreach ($optres as $o) {
				$optionsdata[$o->opt_id]=$o->opt_txt;
			}

			// Save results
			$flist = $this->getQuestions($pollid,false);
			foreach ($flist as $fl) {
				$fieldname = 'q_'.$fl->q_id;
				$cmres=new stdClass();
				$cmres->res_user=$user->id;
				$cmres->res_poll=$pollid;
				$cmres->res_qid=$fl->q_id;
				$cmres->res_ans=$db->escape($item->$fieldname);
				$cmres->res_cm=$subid;
                if (isset($other->$fieldname)) {
                    $cmres->res_ans_other = $db->escape( $other->$fieldname );
                } else {
                    $cmres->res_ans_other = "";
                }
                if (isset($otherAlt->$fieldname)) {
                    $cmres->res_ans_other_alt = $db->escape( $otherAlt->$fieldname );
                } else {
                    $cmres->res_ans_other_alt = "";
                }
				if (!$db->insertObject('#__mpoll_results',$cmres)) {
					$this->setError("Error saving additional information.  Please resubmit.");
					return false;
				}
			}

			// Results email
			if ($pollinfo->poll_resultsemail) {
				$flist = $this->getQuestions($pollid,false);
				$resultsemail = 'A submission has been made to the form <strong>'.$pollinfo->poll_name.'</strong> with  ID #<strong>'.$subid.'</strong> Submission data is below.<br /><br />';
				foreach ($flist as $d) {
					if ($d->q_type != "message" && $d->q_type != "header") {
						$fieldname = 'q_'.$d->q_id;
						$resultsemail .= "<b>".$d->q_text.'</b><br />';
						if ($d->q_type=="attach") {
							if($item->$fieldname) {
								$uploaded_files = explode(",",$item->$fieldname);
								foreach ($uploaded_files as $uf) {
									$resultsemail .= 'Download: <a href="' . JURI::base() . $uf . '">'.basename($uf).'</a><br>';
								}
							}
						} else if ($d->q_type=="cbox") {
                            if($item->$fieldname) {
                                if ($item->$fieldname == 1) {
                                    $resultsemail .= "Yes";
                                } else {
                                    $resultsemail .= "No";
                                }
                            } else {
                                $resultsemail .= "No";
                            }
                            $resultsemail .= '<br />';
                            $resultsemail .= '<br />';
                        } else if (in_array($fieldname,$optfs)) {
							$resultsemail .= $optionsdata[$item->$fieldname];
							if ($other->$fieldname) $resultsemail .= ': '.$other->$fieldname;
							$resultsemail .= '<br />';
							$resultsemail .= '<br />';
						}  else if (in_array($fieldname,$moptfs)) {
							$ans = explode(" ",$item->$fieldname);
							foreach ($ans as $i) {
								$resultsemail .= $optionsdata[$i].'<br />';
							}
                            if ($other->$fieldname) $resultsemail .= 'Other: '.$other->$fieldname;
                            $resultsemail .= '<br />';
							$resultsemail .= '<br />';
						} else {
							$resultsemail .= $item->$fieldname.'<br />';
						}
					}
				}
				$resultsemail .= "<br /><br /><b>User Agent:</b> ".$_SERVER['HTTP_USER_AGENT'];
				$resultsemail .= "<br /><b>IP:</b> ".$this->getIPAddress();

				$replyTo = null;
				if ($pollinfo->poll_emailreplyto) {
					$replyToField = 'q_'.$pollinfo->poll_emailreplyto;
					if ($item->$replyToField) $replyTo = $item->$replyToField;
				}


				// Email Results by Options
				if (count($pollinfo->poll_results_emails)) {
					foreach ($pollinfo->poll_results_emails as $re) {
						$option = explode("_",$re['option']);
						$question = 'q_'.$option[0];
						$answer = $option[1];
						if ($item->$question == $answer) {
							$emllist = Array();
							$emllist = explode(",",$re['emailto']);
							$mail = JFactory::getMailer();
							$sent = $mail->sendMail( $jconfig->get( 'mailfrom' ), $jconfig->get( 'fromname' ), $emllist, $re['subject'], $resultsemail, true, null, null, null, $replyTo );
						}
					}
				}

				// All Results email
				if ($pollinfo->poll_emailto) {
					$emllist = Array();
					$emllist = explode(",",$pollinfo->poll_emailto);
					$mail = JFactory::getMailer();
					$sent = $mail->sendMail( $jconfig->get( 'mailfrom' ), $jconfig->get( 'fromname' ), $emllist, $pollinfo->poll_emailsubject, $resultsemail, true, null, null, null, $replyTo );
				}
			}

			// Confirmation email
			if ($pollinfo->poll_confemail && $pollinfo->poll_confemail_to) {
				$flist = $this->getQuestions($pollid,false);
				$completedid = base64_encode('cmplid='.$subid.'&id=' . $pubid);
				$cmplurl = JUri::root().JRoute::_('index.php?option=com_mpoll&task=results&poll='.$pollinfo->poll_id. '&cmpl=' . $completedid,false);
                if ($paymentNeeded || $subNeeded) $payurl = JUri::root().JRoute::_('index.php?option=com_mpoll&task=pay&poll='.$pollinfo->poll_id. '&payment=' . $completedid,false);
                else $payurl = "";

				$confemail = $pollinfo->poll_confmsg;
				$confemail = str_replace("{name}",$user->name,$confemail);
				$confemail = str_replace("{username}",$user->username,$confemail);
				$confemail = str_replace("{email}",$user->email,$confemail);
				$confemail = str_replace("{resid}",$subid,$confemail);
				$confemail = str_replace("{resurl}",$cmplurl,$confemail);
				$confemail = str_replace("{payurl}",$payurl,$confemail);
				foreach ($flist as $d) {
					$fieldname = 'q_'.$d->q_id;
					if ($d->q_type=="attach") {

					} else if (in_array($fieldname,$optfs)) {
						$youropts="";
						$youropts = $optionsdata[$item->$fieldname];
						if ($other->$fieldname) $youropts .= ': '.$other->$fieldname;
						$confemail = str_replace("{i".$d->q_id."}",$youropts,$confemail);
					} else if (in_array($fieldname,$moptfs)) {
						$youropts="";
						$ans = explode(" ",$item->$fieldname);
						foreach ($ans as $i) {
							$youropts .= $optionsdata[$i].' ';
						}
						$confemail = str_replace("{i".$d->q_id."}",$youropts,$confemail);
					} else {
						$confemail = str_replace("{i".$d->q_id."}",$item->$fieldname,$confemail);
					}
				}
				$confTo = null;
				if ($pollinfo->poll_confemail_to) {
					$confToField = 'q_'.$pollinfo->poll_confemail_to;
					if ($item->$confToField) $confTo = $item->$confToField;
				}
				$mail = &JFactory::getMailer();
				$sent = $mail->sendMail ($pollinfo->poll_conffromemail, $pollinfo->poll_conffromname, $confTo, $pollinfo->poll_confsubject, $confemail, true);
			}

		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return $subid;
	}

	function getCasted($pollid) {
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		if (!$user->id) return false;

		$query=$db->getQuery(true);
		$query->select('cm_id');
		$query->from('#__mpoll_completed');
		$query->where('cm_user='.$user->id);
		$query->where('cm_poll='.$db->escape($pollid));
		$db->setQuery($query);
		$data = $db->loadColumn();

		if (count($data)) return true;
		else return false;
	}

	function getCompletition($cmid,$pubid = null) {
		$db = JFactory::getDBO();
		$q = $db->getQuery(true);
		$q->select('*');
		$q->from('#__mpoll_completed');
		$q->where('cm_id = '.$db->escape($cmid));
		if ($pubid) $q->where('cm_pubid = "'.$pubid.'"');
		$q->setLimit('1');
		$db->setQuery($q);
		$data = $db->loadObject();
		return $data;
	}
	function getFirstCast($pollid) {
		$db = JFactory::getDBO();
		$q = $db->getQuery(true);
		$q->select('cm_time');
		$q->from('#__mpoll_completed');
		$q->where('cm_poll = '.$db->escape($pollid));
		$q->order('cm_time ASC');
		$q->setLimit('1');
		$db->setQuery($q);
		$data = $db->loadAssoc();
		return $data['cm_time'];
	}
	function getLastCast($pollid) {
		$db = JFactory::getDBO();
		$q = $db->getQuery(true);
		$q->select('cm_time');
		$q->from('#__mpoll_completed');
		$q->where('cm_poll = '.$db->escape($pollid));
		$q->order('cm_time DESC');
		$q->setLimit('1');
		$db->setQuery($q);
		$data = $db->loadAssoc();
		return $data['cm_time'];
	}
	function getNumCast($pollid) {
		$db = JFactory::getDBO();
		$q = $db->getQuery(true);
		$q->select('count(*)');
		$q->from('#__mpoll_completed');
		$q->where('cm_poll = '.$db->escape($pollid));
		$q->group('cm_poll');
		$db->setQuery($q);
		$count = $db->loadResult();
		return (int)$count;
	}

	function applyAnswers($qdata,$cmplid) {
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		foreach ($qdata as &$q) {
			$qa = $db->getQuery(true);
			$qa->select('res_ans');
			$qa->from('#__mpoll_results');
			$qa->where('res_user='.$user->id);
			$qa->where('res_qid='.$q->q_id);
			$qa->where('res_cm='.$cmplid);
			$db->setQuery($qa);
			$q->answer=$db->loadResult();
		}
		return $qdata;
	}

    public function getAllResults($pdata,$filterQuestions) {
        $db = JFactory::getDBO();
        $app=Jfactory::getApplication();
        $cfg = MPollHelper::getConfig();
        $pollid = $pdata->poll_id;

        $filterResults = [];
        $data = [];
        $filterNada = false;

        foreach ($filterQuestions as $qu) {
            $ans = $app->getUserState('filter.qf_'.$qu->q_id, '');
            if ($ans != '' && !$filterNada) {
                if ($qu->q_type == 'textbox' || $qu->q_type == 'textar' || $qu->q_type == 'mailchimp' || $qu->q_type == 'email' || $qu->q_type == 'datedropdown' ) {
                    $qo=$db->getQuery(true);
                    $qo->select('res_cm');
                    $qo->from('#__mpoll_results');
                    $qo->where('LOWER(res_ans) LIKE "%'.strtolower($ans).'%"');
                    $qo->where('res_qid = "'.$qu->q_id.'"');
                    $db->setQuery($qo);
                    $results = $db->loadColumn();
                    $filterResults[] = $results;
                    if (count($results) == 0) $filterNada = true;
                }
                if ($qu->q_type == 'gmap') {
                    if ($cfg->gmaps_geocode_key) {
                        $searchDistance = $app->getUserState('filter.qf_'.$qu->q_id.'_distance', 50);
                        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($ans) . '&key=' . $cfg->gmaps_geocode_key;
                        $gdata_json = $this->curl_file_get_contents($url);
                        $gdata = json_decode($gdata_json);
                        $lat = $gdata->results[0]->geometry->location->lat; //latitude
                        $lon = $gdata->results[0]->geometry->location->lng; //longitude
                        if (!$lat || !$lon) {
                            $filterResults[] = [];
                            $filterNada = true;
                        } else {
                            $query = "SELECT res_cm, ( 3959 * acos( cos( radians('" . $lat . "') ) * cos( radians( res_ans_other ) ) * cos( radians( res_ans_other_alt ) - radians('" . $lon . "') ) + sin( radians('" . $lat . "') ) * sin( radians( res_ans_other ) ) ) ) AS distance FROM #__mpoll_results ";
                            $query .= "HAVING distance < '" . $searchDistance . "' ";
                            $query .= "ORDER BY distance ";
                            $db->setQuery($query);
                            $results = $db->loadColumn();
                            $filterResults[] = $results;
                            if (count($results) == 0) $filterNada = true;
                        }
                    }
                }
                if ($qu->q_type == 'multi' || $qu->q_type == 'dropdown' || $qu->q_type == 'cbox') {
                    $qo=$db->getQuery(true);
                    $qo->select('res_cm');
                    $qo->from('#__mpoll_results');
                    $qo->where('res_ans = "'.$ans.'"');
                    $qo->where('res_qid = "'.$qu->q_id.'"');
                    $db->setQuery($qo);
                    $results = $db->loadColumn();
                    $filterResults[] = $results;
                    if (count($results) == 0) $filterNada = true;
                }
                if ($qu->q_type == 'mcbox' || $qu->q_type=="mlist") {
                    $qo=$db->getQuery(true);
                    $qo->select('res_cm,res_ans');
                    $qo->from('#__mpoll_results');
                    $qo->where('res_qid = "'.$qu->q_id.'"');
                    $db->setQuery($qo);
                    $multiResults = $db->loadObjectList();
                    $filterResult = [];
                    foreach ($multiResults as $multiResult) {
                        $resArray = explode(' ', $multiResult->res_ans);
                        if (in_array($ans, $resArray)) {
                            $filterResult[] = $multiResult->res_cm;
                        }
                    }
                    $filterResults[] = $filterResult;
                    if (count($filterResult) == 0) $filterNada = true;
                }
            }
        }

        $filteredIds = [];
        $genResults = true;
        $genFeatured = false;
        if (count($filterResults)) {
            $this->hasFilters = true;
            $filteredIds = call_user_func_array('array_intersect',$filterResults);
        }

        if (!$this->hasFilters && !$pdata->poll_results_showall) {
            $genResults = false;
        }

        //poll_results_showfeat
        if (!$genResults && $pdata->poll_results_showfeat) {
            $genResults = true;
            $genFeatured = true;
        }

        if (!$filterNada && $genResults) {
            $query = $db->getQuery(true);
            $query->select('*');
            $query->from('#__mpoll_completed AS r');
            $query->where('r.published = 1');
            $query->where('r.cm_poll = ' . $db->escape($pollid));
            if ($genFeatured) $query->where('r.featured = 1');
            if (count($filteredIds)) $query->where('r.cm_id IN (' . implode(',', $filteredIds) . ')');
            $query->order('r.cm_time DESC');
            $db->setQuery($query);
            $data = $db->loadObjectList();

            //Get Results
            foreach ($data as &$d) {
                $resQuery = $db->getQuery(true);

                $resQuery->select('*');
                $resQuery->from('#__mpoll_results AS r');
                $resQuery->where('res_cm = ' . $db->escape($d->cm_id));
                $db->setQuery($resQuery);
                $cmd = $db->loadObjectList();

                foreach ($cmd as $c) {
                    $fn = 'q_' . $c->res_qid;
                    $fno = 'q_' . $c->res_qid . '_other';
                    $fna = 'q_' . $c->res_qid . '_other_alt';
                    $d->$fn = stripslashes($c->res_ans);
                    $d->$fno = stripslashes($c->res_ans_other);
                    $d->$fna = stripslashes($c->res_ans_other_alt);
                }
            }
        }

        // Sort 2
        if ($pdata->poll_results_sortby2 != $pdata->poll_results_sortby) {
            $sortField2 = null;
            if ($pdata->poll_results_sortby2 == 0) {
                $sortField2 = 'cm_id';
            } else {
                $sortField2 = 'q_' . $pdata->poll_results_sortby2;
            }
            if ($pdata->poll_results_sortdirr2 == 'ASC') {
                usort($data, fn($a, $b) => strcmp($a->$sortField2, $b->$sortField2));
            } else {
                usort($data, fn($a, $b) => strcmp($b->$sortField2, $a->$sortField2));
            }
        }

        // Sort 1
        $sortField = null;
        if ($pdata->poll_results_sortby == -1) {
            $sortField = 'featured';
        } else if ($pdata->poll_results_sortby == 0) {
            $sortField = 'cm_id';
        } else {
            $sortField = 'q_'.$pdata->poll_results_sortby;
        }
        if ($pdata->poll_results_sortdirr == 'ASC') {
            usort($data, fn($a, $b) => strcmp($a->$sortField, $b->$sortField));
        } else {
            usort($data, fn($a, $b) => strcmp($b->$sortField, $a->$sortField));
        }

        return $data;
    }

    public function getResultsMessage($pollData, $itemCount)
    {
        if ($this->hasFilters == false && $itemCount == 0 && !$pollData->poll_results_showall) {
            // no filter msg
            return $pollData->poll_results_msg_filterfirst;
        } else if ($itemCount == 0) {
            //no items msg
            return $pollData->poll_results_msg_noresults;
        }
        return "";
    }

    public function getFilterForm($pollid)
    {
        $app=Jfactory::getApplication();
        $cfg = MPollHelper::getConfig();
        $jinput = JFactory::getApplication()->input;
        $filterQuestions = $this->getQuestions($pollid,true,false,true);
        $form = new Form('filterForm');
        $baseForm='<?xml version="1.0" encoding="UTF-8"?><form><fields name="filter"></fields></form>';
        $form->load(new \SimpleXMLElement($baseForm));
        $filterrData = $jinput->getVar('filter', array(), 'post', 'array');

        foreach ($filterQuestions as $qu) {
            if (isset($filterrData['qf_' . $qu->q_id])) $requestAns = $filterrData['qf_' . $qu->q_id];
            else $requestAns = null;
            $stateAns = $app->getUserState('filter.qf_'.$qu->q_id, '');
            if ($requestAns == "") $ans='';
            else if ($requestAns != null) $ans = $requestAns;
            else $ans = $stateAns;
            $app->setUserState('filter.qf_'.$qu->q_id, $ans);

            if ($qu->q_type == 'gmap') {
                if ($cfg->gmaps_geocode_key) {
                    if (isset($filterrData['qf_' . $qu->q_id.'_distance'])) $requestAnsD = $filterrData['qf_' . $qu->q_id.'_distance'];
                    else $requestAnsD = null;
                    $stateAnsD = $app->getUserState('filter.qf_'.$qu->q_id.'_distance', '');
                    if ($requestAnsD == "") $ansd='';
                    else if ($requestAnsD != null) $ansd = $requestAnsD;
                    else $ansd = $stateAnsD;
                    $app->setUserState('filter.qf_'.$qu->q_id.'_distance', $ansd);
                }
            }

            if ($qu->q_type == 'textbox' || $qu->q_type == 'textar' || $qu->q_type == 'mailchimp' || $qu->q_type == 'email' || $qu->q_type == 'datedropdown') {
                $formxml = '<field name="qf_'.$qu->q_id.'" type="text" default="'.$ans.'" label="'.$qu->q_text.'" hint="'.$qu->q_filter_name.'" description="" />';
                $newField = new \SimpleXMLElement($formxml);
                $form->setField($newField,'filter');
                $this->filter_fields[] = 'qf_'.$qu->q_id.'';
            }
            if ($qu->q_type == 'gmap') {
                $formxml = '<field name="qf_'.$qu->q_id.'" type="text" default="'.$ans.'" label="'.$qu->q_filter_name.'" hint="'.$qu->q_filter_name.'" description="" />';
                $newField = new \SimpleXMLElement($formxml);
                $form->setField($newField,'filter');
                $this->filter_fields[] = 'qf_'.$qu->q_id.'';

                $miles = explode(",",$cfg->gmaps_miles);

                $formxml  = '<field name="qf_'.$qu->q_id.'_distance" type="list" default="'.$ansd.'" label="Distance" description="">';
                foreach ($miles as $m) {
                    $formxml .= '<option value="'.$m.'">'.$m.' miles</option>';
                }
                $formxml .= '</field>';
                $newField = new \SimpleXMLElement($formxml);
                $form->setField($newField,'filter');
                $this->filter_fields[] = 'qf_'.$qu->q_id.'';

            }
            if ($qu->q_type == 'multi' || $qu->q_type == 'dropdown' || $qu->q_type == 'mcbox' || $qu->q_type=="mlist") {
                $formxml  = '<field name="qf_'.$qu->q_id.'" type="list" default="'.$ans.'" label="'.$qu->q_filter_name.'" description="">';
                $formxml .= '<option value=""><![CDATA['.$qu->q_filter_name.']]></option>';
                foreach ($qu->options as $quo) {
                    $formxml .= '<option value="'.$quo->value.'"><![CDATA['.$quo->text.']]></option>';
                }
                $formxml .= '</field>';
                $newField = new \SimpleXMLElement($formxml);
                $form->setField($newField,'filter');
                $this->filter_fields[] = 'qf_'.$qu->q_id.'';
            }
            if ($qu->q_type == 'cbox') {
                $formxml  = '<field name="qf_'.$qu->q_id.'" type="list" default="'.$ans.'" label="'.$qu->q_filter_name.'" description="">';
                $formxml .= '<option value="">'.$qu->q_filter_name.'</option>';
                $formxml .= '<option value="1">Yes</option>';
                $formxml .= '<option value="0">No</option>';
                $formxml .= '</field>';
                $newField = new \SimpleXMLElement($formxml);
                $form->setField($newField,'filter');
                $this->filter_fields[] = 'qf_'.$qu->q_id.'';
            }
        }

        return $form;
    }

	public static function canUpload($file,&$err)
	{
		$params = JComponentHelper::getParams('com_media');

		//Check for File
		if (empty($file['name'])) {
			$err="No File.  Please resubmit.";
			return false;
		}

		//Check filename is safe
		jimport('joomla.filesystem.file');
		if ($file['name'] !== JFile::makesafe($file['name'])) {
			$err="Bad file name. Please resubmit.";
			return false;
		}

		$format = strtolower(JFile::getExt($file['name']));

		//Check if type allowed
		if (JVersion::MAJOR_VERSION == 3) $allowable = explode(',', $params->get('upload_extensions'));
		else $allowable = explode(',', $params->get('restrict_uploads_extensions'));
		$ignored = explode(',', $params->get('ignore_extensions'));
		if (!in_array($format, $allowable) && !in_array($format, $ignored))
		{
			$err="Filetype Not Allowed.  Please resubmit.";
			return false;
		}

		//Check for size
		$maxSize = (int) ($params->get('upload_maxsize', 0) * 1024 * 1024);
		if ($maxSize > 0 && (int) $file['size'] > $maxSize)
		{
			$err = 'File too Large.  Please resubmit.';
			return false;
		}


		//other checks
		$xss_check =  file_get_contents($file['tmp_name'], false, null, -1, 256);
		$html_tags = array('abbr', 'acronym', 'address', 'applet', 'area', 'audioscope', 'base', 'basefont', 'bdo', 'bgsound', 'big', 'blackface', 'blink', 'blockquote', 'body', 'bq', 'br', 'button', 'caption', 'center', 'cite', 'code', 'col', 'colgroup', 'comment', 'custom', 'dd', 'del', 'dfn', 'dir', 'div', 'dl', 'dt', 'em', 'embed', 'fieldset', 'fn', 'font', 'form', 'frame', 'frameset', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'head', 'hr', 'html', 'iframe', 'ilayer', 'img', 'input', 'ins', 'isindex', 'keygen', 'kbd', 'label', 'layer', 'legend', 'li', 'limittext', 'link', 'listing', 'map', 'marquee', 'menu', 'meta', 'multicol', 'nobr', 'noembed', 'noframes', 'noscript', 'nosmartquotes', 'object', 'ol', 'optgroup', 'option', 'param', 'plaintext', 'pre', 'rt', 'ruby', 's', 'samp', 'script', 'select', 'server', 'shadow', 'sidebar', 'small', 'spacer', 'span', 'strike', 'strong', 'style', 'sub', 'sup', 'table', 'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'title', 'tr', 'tt', 'ul', 'var', 'wbr', 'xml', 'xmp', '!DOCTYPE', '!--');
		foreach($html_tags as $tag) {
			// A tag is '<tagname ', so we need to add < and a space or '<tagname>'
			if (stristr($xss_check, '<'.$tag.' ') || stristr($xss_check, '<'.$tag.'>')) {
				$err="Bad file.  Please resubmit.";
				return false;
			}
		}
		return true;
	}

	public function PayPalCreate($poll,$completition) {

		$cfg = MPollHelper::getConfig();
		$db = JFactory::getDBO();
		$user = JFactory::getUser();

		$cost = $poll->poll_payment_amount;

		$newpayment=new stdClass();
		$newpayment->pay_cm=$completition->cm_id;
		$newpayment->pay_poll=$poll->poll_id;
        $newpayment->pay_sale_type="order";
		$newpayment->pay_type='paypal';
		$newpayment->pay_status='started';
		$newpayment->pay_amount=floatval($cost);
		$newpayment->pay_updated=date("Y-m-d H:i:s");
		if (!$db->insertObject('#__mpoll_payment',$newpayment)) {
			$this->setError("Error creating payment");
			return false;
		}
		$newpaymentid = $db->insertid();

		$invoicenum = (1000+$poll->poll_id).'-'.(1000000+$completition->cm_id).'-'.str_pad($newpaymentid,8,0,STR_PAD_LEFT);
		$setinvoice = $db->getQuery(true);
		$setinvoice->update('#__mpoll_payment');
		$setinvoice->set('pay_invoice = "'.$db->escape($invoicenum).'"');
		$setinvoice->where('pay_id = '.$newpaymentid);
		$db->setQuery($setinvoice);
		$db->execute();

        $orderItems = [];
        $totalCost = $poll->poll_payment_amount;

        $item = [];
        $item['quantity'] = 1;
        $item['unit_amount']['value'] = number_format($totalCost,2,".","");
        $item['unit_amount']['currency_code'] = 'USD';
        $item['category'] = 'DIGITAL_GOODS';
        $item['name'] = $poll->poll_payment_title;
        $orderItems[] = $item;

        // setup amount
        $amount = [
            'currency_code' => 'USD',
            'value' => number_format($totalCost,2,".",""),
            'breakdown' => [
                'item_total' => [
                    'currency_code' => 'USD',
                    'value' => number_format($totalCost,2,".","")
                ]
            ]
        ];

        $order['invoice_id'] = $invoicenum;
        $order['description'] = $poll->poll_payment_title." #".$invoicenum;
        $order['items'] = $orderItems;
        $order['amount'] = $amount;

        // setup body
        $body = [
            'intent' => 'CAPTURE',
            'payment_source'=>[
                'paypal'=>[
                    'experience_context' => [
                        'return_url' => JUri::root().JRoute::_( 'index.php?option=com_mpoll&task=pay&poll='.$poll->poll_id.'&payment=' . base64_encode('cmplid='.$completition->cm_id.'&id=' . $completition->cm_pubid),false,true ),
                        'cancel_url' => JUri::root().JRoute::_( 'index.php?option=com_mpoll&task=paypal_cancel_link&poll='.$poll->poll_id.'&payment=' . base64_encode('cmplid='.$completition->cm_id.'&id=' . $completition->cm_pubid),false,true ),
                        'shipping_preference' => 'NO_SHIPPING'
                    ]
                ]
            ],
            'purchase_units' => [
                $order
            ]
        ];



        if (!$payPayService = new PayPalService($cfg->paypal_api_id,$cfg->paypal_api_secret,$cfg->paypal_mode)) {
            $this->setError(json_encode($payPayService->error));
            return false;
        }
        if (!$ppResult = $payPayService->createOrder($body)) {
            $this->setError(json_encode($payPayService->error));
            return false;
        }


        if ($ppResult['status'] == 'PAYER_ACTION_REQUIRED') {
            $setstatus = $db->getQuery(true);
            $setstatus->update('#__mpoll_payment');
            $setstatus->set('pay_status = "created"');
            $setstatus->set('pay_updated = "'.date("Y-m-d H:i:s").'"');
            $setstatus->where('pay_id = '.$newpaymentid);
            $db->setQuery($setstatus);
            $db->execute();

            return json_encode($ppResult);
        }

        $this->setError(json_encode($payPayService->error));
        return false;
	}



	public function PayPalExecute($poll,$completition) {

		$cfg = MPollHelper::getConfig();
		$db = JFactory::getDBO();
		$user = JFactory::getUser();

		$jinput = JFactory::getApplication()->input;

		$captureId = $jinput->getVar('capture');

        if (!$captureId) {
            $this->setError("Capture ID Not provided");
            return false;
        }

        $payPalService = new PayPalService($cfg->paypal_api_id,$cfg->paypal_api_secret,$cfg->paypal_mode);

        if ($ppResult = $payPalService->getCapture($captureId)) {
            // Get order id as it may have changed
            $ppOrderId = $ppResult['supplementary_data']['related_ids']['order_id'];
        } else {
            $this->setError(json_encode($payPayService->error));
            return false;
        }

        $paymentquery = $db->getQuery(true);
        $paymentquery->select('*');
        $paymentquery->from('#__mpoll_payment');
        $paymentquery->where('pay_invoice = "'.$ppResult['invoice_id'].'"');
        $paymentquery->where('pay_cm = '.$completition->cm_id);
        $db->setQuery( $paymentquery );
        $payment = $db->loadObject();
        if (!$payment) {
            $this->setError("Could not find Payment");
            return false;
        }

        $setstatus = $db->getQuery(true);
        $setstatus->update('#__mpoll_payment');
        $setstatus->set('pay_status = "captured"');
        $setstatus->set('pay_updated = "'.date("Y-m-d H:i:s").'"');
        $setstatus->set('pay_payid = "'.$ppOrderId.'"');
        $setstatus->where('pay_id = '.$payment->pay_id);
        $db->setQuery($setstatus);
        $db->execute();

        if ($ppResult['status'] == 'COMPLETED') {
            // Update compleittion Record
            $setcmstatus = $db->getQuery(true);
            $setcmstatus->update('#__mpoll_completed');
            $setcmstatus->set('cm_status = "paid"');
            $setcmstatus->where('cm_id = '.$payment->pay_cm);
            $db->setQuery($setcmstatus);
            $db->execute();

            // User Notification Email
            if ($poll->poll_payment_to) {
                $qa = $db->getQuery(true);
                $qa->select('res_ans');
                $qa->from('#__mpoll_results');
                $qa->where('res_qid='.$poll->poll_payment_to);
                $qa->where('res_cm='.$payment->pay_cm);
                $db->setQuery($qa);
                $toEmail=$db->loadResult();

                if ($toEmail) {
                    $mail = &JFactory::getMailer();
                    $sent = $mail->sendMail( $poll->poll_payment_fromemail, $poll->poll_payment_fromname, $toEmail, $poll->poll_payment_subject, $poll->poll_payment_body, true );
                }
            }

            // Admin Notification Email
            if ($poll->poll_payment_adminemail) {
                $resultsemail = 'A payment has been made to the form <strong>'.$poll->poll_name.'</strong> with  ID #<strong>'.$payment->pay_cm.'</strong>';

                $emllist = Array();
                $emllist = explode(",",$poll->poll_payment_adminemail);

                $replyTo = null;
                if ($poll->poll_payment_to) {
                    $replyToField = 'q_'.$poll->poll_payment_to;
                    if ($item->$replyToField) $replyTo = $item->$replyToField;
                }

                $mail = &JFactory::getMailer();
                $sent = $mail->sendMail ($poll->poll_payment_fromemail, $poll->poll_payment_fromname, $emllist, $poll->poll_payment_adminsubject, $resultsemail, true, null, null, null, $replyTo);
            }
        }

        return true;
	}

	public function PayPalCancel($poll,$completition) {
		$cfg = MPollHelper::getConfig();
		$db   = JFactory::getDBO();
		$user = JFactory::getUser();
		$jinput = JFactory::getApplication()->input;

		$paymentId = $jinput->getVar('paymentID');

		$paymentquery = $db->getQuery(true);
		$paymentquery->select('*');
		$paymentquery->from('#__mpoll_payment');
		$paymentquery->where('pay_cm = '.$completition->cm_id);
		$paymentquery->where("pay_status IN ('started','created')");
		$paymentquery->order('pay_created DESC');
		$db->setQuery( $paymentquery );
		$payment = $db->loadObject();
		if (!$payment) {
			$this->setError("Could not find Payment");
			return false;
		}

		$setstatus = $db->getQuery(true);
		$setstatus->update('#__mpoll_payment');
		$setstatus->set('pay_status = "cancelled"');
		$setstatus->set('pay_updated = "'.date("Y-m-d H:i:s").'"');
		$setstatus->where('pay_id = '.$payment->pay_id);
		$db->setQuery($setstatus);
		$db->execute();

		$setcmstatus = $db->getQuery(true);
		$setcmstatus->update('#__mpoll_completed');
		$setcmstatus->set('cm_status = "unpaid"');
		$setcmstatus->where('cm_id = '.$payment->pay_cm);
		$db->setQuery($setcmstatus);
		$db->execute();

		return true;
	}

	public function PayPalWebhook() {
		$cfg = MPollHelper::getConfig();
		$db   = JFactory::getDBO();
		$jinput = JFactory::getApplication()->input;
		$jsondata = $jinput->json->getRaw();
		$headers = array_change_key_case($this->getHeaders(), CASE_UPPER);
		if ($jsondata) {
			$content = json_decode($jsondata,true);
			$whid = $content['id'];
			$invoice = $content['resource']['invoice_number'];
			$state = $content['resource']['state'];
            $captureId = $content['resource']['id'];

            $payPalService = new PayPalService($cfg->paypal_api_id,$cfg->paypal_api_secret,$cfg->paypal_mode);

            // Subscription Activated
            if ($content['event_type'] == "BILLING.SUBSCRIPTION.ACTIVATED" || $content['event_type'] == "BILLING.SUBSCRIPTION.UPDATED") {
                $invoiceId = $content['resource']['custom_id'];
                $subId = $content['resource']['id'];

                $paymentquery = $db->getQuery(true);
                $paymentquery->select('*');
                $paymentquery->from('#__mpoll_payment');
                $paymentquery->where('pay_invoice = "'.$invoiceId.'"');
                $paymentquery->where('pay_payid = "'.$subId.'"');
                $db->setQuery( $paymentquery );
                $payment = $db->loadObject();
                if (!$payment) {
                    return true;
                } else {
                    if (!$ppResult = $payPalService->getSubscription($subId)) {
                        return false;
                    }
                    if ( $ppResult['status'] == 'ACTIVE' ) {
                        // do the dates
                        $currentDate = new Date('now');
                        $endDate = new Date($ppResult['billing_info']['next_billing_time']);
                        $end = $endDate->format("Y-m-t");

                        $setcmstatus = $db->getQuery(true);
                        $setcmstatus->update('#__mpoll_completed');
                        $setcmstatus->set('cm_status = "subscribed"');
                        $setcmstatus->set('cm_end = "' . $end . '"');
                        $setcmstatus->where('cm_id = ' . $payment->pay_cm);
                        $db->setQuery($setcmstatus);
                        $db->execute();
                    }
                }
            }

            if ($content['event_type'] == "BILLING.SUBSCRIPTION.EXPIRED" || $content['event_type'] == "BILLING.SUBSCRIPTION.CANCELLED" || $content['event_type'] == "BILLING.SUBSCRIPTION.SUSPENDED") {
                $invoiceId = $content['resource']['custom_id'];
                $subId = $content['resource']['id'];

                $paymentquery = $db->getQuery(true);
                $paymentquery->select('*');
                $paymentquery->from('#__mpoll_payment');
                $paymentquery->where('pay_invoice = "'.$invoiceId.'"');
                $paymentquery->where('pay_payid = "'.$subId.'"');
                $db->setQuery( $paymentquery );
                $payment = $db->loadObject();
                if (!$payment) {
                    return true;
                } else {
                    if (!$ppResult = $payPalService->getSubscription($subId)) {
                        return false;
                    }

                    $subStatus = "";
                    if ( $ppResult['status'] == 'EXPIRED' ) $subStatus = "subexpired";
                    if ( $ppResult['status'] == 'CANCELLED' ) $subStatus = "subcancelled";
                    if ( $ppResult['status'] == 'SUSPENDED' ) $subStatus = "subsuspended";

                    $setcmstatus = $db->getQuery(true);
                    $setcmstatus->update('#__mpoll_completed');
                    $setcmstatus->set('cm_status = "'.$subStatus.'"');
                    $setcmstatus->where('cm_id = ' . $payment->pay_cm);
                    $db->setQuery($setcmstatus);
                    $db->execute();
                }
            }

            if ($content['event_type'] == "BILLING.SUBSCRIPTION.PAYMENT.FAILED") {
                $invoiceId = $content['resource']['custom_id'];
                $subId = $content['resource']['id'];

                $paymentquery = $db->getQuery(true);
                $paymentquery->select('*');
                $paymentquery->from('#__mpoll_payment');
                $paymentquery->where('pay_invoice = "'.$invoiceId.'"');
                $paymentquery->where('pay_payid = "'.$subId.'"');
                $db->setQuery( $paymentquery );
                $payment = $db->loadObject();
                if (!$payment) {
                    return true;
                } else {
                    if (!$ppResult = $payPalService->getSubscription($subId)) {
                        return false;
                    }

                    $setcmstatus = $db->getQuery(true);
                    $setcmstatus->update('#__mpoll_completed');
                    $setcmstatus->set('cm_status = "subpayfailed"');
                    $setcmstatus->where('cm_id = ' . $payment->pay_cm);
                    $db->setQuery($setcmstatus);
                    $db->execute();
                }
            }

            if ($content['event_type'] == "PAYMENT.SALE.COMPLETED") {
                if (isset($content['resource']['billing_agreement_id'])) {
                    $invoiceId = $content['resource']['custom'];
                    $subId = $content['resource']['billing_agreement_id'];

                    $paymentquery = $db->getQuery(true);
                    $paymentquery->select('*');
                    $paymentquery->from('#__mpoll_payment');
                    $paymentquery->where('pay_invoice = "'.$invoiceId.'"');
                    $paymentquery->where('pay_payid = "'.$subId.'"');
                    $db->setQuery( $paymentquery );
                    $payment = $db->loadObject();
                    if (!$payment) {
                        return true;
                    } else {
                        if (!$ppResult = $payPalService->getSubscription($subId)) {
                            return false;
                        }
                        if ( $ppResult['status'] == 'ACTIVE' ) {
                            // do the dates
                            $currentDate = new Date('now');
                            $endDate = new Date($ppResult['billing_info']['next_billing_time']);
                            $end = $endDate->format("Y-m-t");

                            $setcmstatus = $db->getQuery(true);
                            $setcmstatus->update('#__mpoll_completed');
                            $setcmstatus->set('cm_status = "subscribed"');
                            $setcmstatus->set('cm_end = "' . $end . '"');
                            $setcmstatus->where('cm_id = ' . $payment->pay_cm);
                            $db->setQuery($setcmstatus);
                            $db->execute();
                        }
                    }
                }
            }

            // Payment Complete
            if ($content['event_type'] == "PAYMENT.CAPTURE.COMPLETED") {
                $ppOrderId = $content['resource']['supplementary_data']['related_ids']['order_id'];
                $invoiceId = $content['resource']['invoice_id'];
                $paymentquery = $db->getQuery(true);
                $paymentquery->select('*');
                $paymentquery->from('#__mpoll_payment');
                $paymentquery->where('pay_invoice = "'.$invoiceId.'"');
                $paymentquery->where('pay_payid = "'.$ppOrderId.'"');
                $db->setQuery( $paymentquery );
                $payment = $db->loadObject();
                if (!$payment) {
                    return true;
                } else {
                    if (!$ppResult = $payPalService->getOrder($ppOrderId)) {
                        $this->setError(json_encode($payPayService->error));
                        return "false";
                    }
                    if ( $ppResult['status'] == 'COMPLETED' ) {
                        $setcertstatus = $db->getQuery(true);
                        $setcertstatus->update('#__mpoll_completed');
                        $setcertstatus->set('cm_status = "paid"');
                        $setcertstatus->where('cm_id = '.$payment->pay_cm);
                        $db->setQuery($setcertstatus);
                        $db->execute();
                    }
                }
            }
		}
		return 'true';
	}

    public function PayPalCreateSub($poll,$completition) {

        $cfg = MPollHelper::getConfig();
        $db = JFactory::getDBO();
        $user = JFactory::getUser();

        $cost = $poll->poll_payment_amount;

        $newpayment=new stdClass();
        $newpayment->pay_cm=$completition->cm_id;
        $newpayment->pay_poll=$poll->poll_id;
        $newpayment->pay_sale_type="subscription";
        $newpayment->pay_type='paypal';
        $newpayment->pay_status = "created";
        $newpayment->pay_amount=floatval($cost);
        $newpayment->pay_updated=date("Y-m-d H:i:s");
        if (!$db->insertObject('#__mpoll_payment',$newpayment)) {
            $this->setError("Error creating payment");
            return false;
        }
        $newpaymentid = $db->insertid();

        $invoicenum = (1000+$poll->poll_id).'-'.(1000000+$completition->cm_id).'-'.str_pad($newpaymentid,8,0,STR_PAD_LEFT);
        $setinvoice = $db->getQuery(true);
        $setinvoice->update('#__mpoll_payment');
        $setinvoice->set('pay_invoice = "'.$db->escape($invoicenum).'"');
        $setinvoice->where('pay_id = '.$newpaymentid);
        $db->setQuery($setinvoice);
        $db->execute();

        $createSub = [];

        $createSub['plan_id'] = $poll->poll_payment_subplan;
        $createSub['custom_id'] = $invoicenum;

        $applicationContext = [];
        $applicationContext['shipping_preference'] = "NO_SHIPPING";
        $createSub['application_context'] = $applicationContext;

        return json_encode($createSub);

    }

    public function PayPalActivateSub($poll,$completition) {
        $cfg = MPollHelper::getConfig();
        $db = JFactory::getDBO();
        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;

        $subId = $jinput->getVar('subscription');

        if (!$subId) {
            $this->setError("Capture ID Not provided");
            return false;
        }

        $payPalService = new PayPalService($cfg->paypal_api_id,$cfg->paypal_api_secret,$cfg->paypal_mode);

        if (!$ppResult = $payPalService->getSubscription($subId)) {
            $this->setError(json_encode($payPayService->error));
            return false;
        }

        $paymentquery = $db->getQuery(true);
        $paymentquery->select('*');
        $paymentquery->from('#__mpoll_payment');
        $paymentquery->where('pay_invoice = "'.$ppResult['custom_id'].'"');
        $paymentquery->where('pay_cm = '.$completition->cm_id);
        $db->setQuery( $paymentquery );
        $payment = $db->loadObject();
        if (!$payment) {
            $this->setError("Could not find Payment");
            return false;
        }

        $setstatus = $db->getQuery(true);
        $setstatus->update('#__mpoll_payment');
        if ($ppResult['status'] == "ACTIVE") $setstatus->set('pay_status = "subscribed"');
        else $setstatus->set('pay_status = "notsubscribed"');
        $setstatus->set('pay_updated = "'.date("Y-m-d H:i:s").'"');
        $setstatus->set('pay_payid = "'.$subId.'"');
        $setstatus->where('pay_id = '.$payment->pay_id);
        $db->setQuery($setstatus);
        $db->execute();

        if ($ppResult['status'] == "ACTIVE") {
            // do the dates
            $currentDate = new Date('now');
            $start = $currentDate->format("Y-m-d");
            $endDate = new Date($ppResult['billing_info']['next_billing_time']);
            $end = $endDate->format("Y-m-t");

            $setcmstatus = $db->getQuery(true);
            $setcmstatus->update('#__mpoll_completed');
            $setcmstatus->set('cm_status = "subscribed"');
            $setcmstatus->set('cm_start = "'.$start.'"');
            $setcmstatus->set('cm_end = "'.$end.'"');
            $setcmstatus->where('cm_id = '.$payment->pay_cm);
            $db->setQuery($setcmstatus);
            $db->execute();

            // User Notification Email
            if ($poll->poll_payment_to) {
                $qa = $db->getQuery(true);
                $qa->select('res_ans');
                $qa->from('#__mpoll_results');
                $qa->where('res_qid='.$poll->poll_payment_to);
                $qa->where('res_cm='.$payment->pay_cm);
                $db->setQuery($qa);
                $toEmail=$db->loadResult();

                if ($toEmail) {
                    $mail = &JFactory::getMailer();
                    $sent = $mail->sendMail( $poll->poll_payment_fromemail, $poll->poll_payment_fromname, $toEmail, $poll->poll_payment_subject, $poll->poll_payment_body, true );
                }
            }

            // Admin Notification Email
            if ($poll->poll_payment_adminemail) {
                $resultsemail = 'A payment has been made to the form <strong>'.$poll->poll_name.'</strong> with  ID #<strong>'.$payment->pay_cm.'</strong>';

                $emllist = Array();
                $emllist = explode(",",$poll->poll_payment_adminemail);

                $replyTo = null;
                if ($poll->poll_payment_to) {
                    $replyToField = 'q_'.$poll->poll_payment_to;
                    if ($item->$replyToField) $replyTo = $item->$replyToField;
                }

                $mail = &JFactory::getMailer();
                $sent = $mail->sendMail ($poll->poll_payment_fromemail, $poll->poll_payment_fromname, $emllist, $poll->poll_payment_adminsubject, $resultsemail, true, null, null, null, $replyTo);
            }
        }

        return true;
    }

    public function PayPalGetPlan($subId) {
        $cfg = MPollHelper::getConfig();
        $payPalService = new PayPalService($cfg->paypal_api_id,$cfg->paypal_api_secret,$cfg->paypal_mode);
        $ppResult = $payPalService->getPlan($subId);
        return $ppResult;
    }

    public function checkForNeededSub($cmpl,$triggerFieldId)
    {
        if ($triggerFieldId == 0) {
            return false;
        }
        $db   = JFactory::getDBO();
        $qa = $db->getQuery(true);
        $qa->select('res_ans');
        $qa->from('#__mpoll_results');
        $qa->where('res_qid='.$triggerFieldId);
        $qa->where('res_cm='.$cmpl->cm_id);
        $db->setQuery($qa);
        $trigger=$db->loadResult();
        if ($trigger == 1) return true;
        return false;
    }

	private function getHeaders() {
		$headers = [];
		foreach ($_SERVER as $name => $value) {
			if (substr($name, 0, 5) == 'HTTP_') {
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}
		return $headers;
	}

	public function AddPaymentLog($payment,$headers=null,$data=null) {
		$db   = JFactory::getDBO();
		$newpaylog=new stdClass();
		$newpaylog->log_payment=$payment;
		if ($headers) $newpaylog->log_headers=$db->escape($headers);
		if ($data) $newpaylog->log_data=$db->escape($data);
		$db->insertObject('#__mpoll_payment_log',$newpaylog);
	}

	public function checkToken($method = 'post', $redirect = true)
	{
		$valid = Session::checkToken($method);

		return $valid;
	}

	public function getIPAddress() {
		//whether ip is from the share internet
		if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}
		//whether ip is from the proxy
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		//whether ip is from the remote address
		else{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}

    private function curl_file_get_contents($URL){
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $URL);
        $contents = curl_exec($c);
        curl_close($c);

        if ($contents) return $contents;
        else return FALSE;
    }


}
