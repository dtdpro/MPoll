<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Mail\Mail;

class MPollModelPollResult extends JModelAdmin
{
    public function getTable($type = 'User', $prefix = 'MUETable', $config = array())
    {
        //there is no table
        return 0;
    }

    public function getForm($data = array(), $loadData = true)
    {
        //there is no form
        return 0;
    }

    public function getItem($pk = null)
    {
        // Initialise variables.
        $input      = JFactory::getApplication()->input;
        $pk = (!empty($pk)) ? $pk : $input->get('id',0);

        //set item variable
        $qu='SELECT * FROM #__mpoll_completed WHERE cm_id = '.$pk;
        $this->_db->setQuery($qu);
        $item = $this->_db->loadObject();

        $gotQuestions = $this->getQuestions($item->cm_poll,false);
        $questions = [];
        foreach ($gotQuestions as $f) {
            $fn = 'q_'.$f->q_id;
            if (!isset($item->$fn)) $item->$fn = '';
            $questions[$f->q_id] = $f;
        }

        //get data for user fields
        $q = $this->_db->getQuery(true);
        $q->select('*');
        $q->from('#__mpoll_results');
        $q->where('res_poll = '.$item->cm_poll);
        $q->where('res_cm = '.$pk);
        $this->_db->setQuery($q);
        $data=$this->_db->loadObjectList();

        foreach ($data as $d) {
            $fieldname = 'q_'.$d->res_qid;
            $item->$fieldname = stripslashes($d->res_ans);
            if ($questions[$d->res_qid]->q_type=="mcbox" || $questions[$d->res_qid]->q_type=="mlist") {
                $item->$fieldname = explode(" ",$item->$fieldname);
            }
        }

        return $item;
    }

    public function save($data)
    {
        $cfg = MPollHelper::getMPollConfig();
        $subid = (int)$data['cm_id'];

        //set item variable
        $qu='SELECT * FROM #__mpoll_completed WHERE cm_id = '.$subid;
        $this->_db->setQuery($qu);
        $submission = $this->_db->loadObject();

        $pollid = $submission->cm_poll;
        $userid = $submission->cm_user;

        $isNew = true;
        $db		= $this->getDbo();
        $app=Jfactory::getApplication();
        $session=JFactory::getSession();

        // Allow an exception to be thrown.
        try
        {
            // Setup item and bind data
            $attachmentFields = array();
            $upfile=array();
            $item = new stdClass();
            $other = new stdClass();
            $otherAlt = new stdClass();
            $flist = $this->getQuestions($pollid,false);

            foreach ($flist as $d) {
                $fieldname = 'q_'.$d->q_id;
                if ($d->q_type == 'attach') {
                    // Do nothing
                    $attachmentFields[] = $d->q_id;
                } else if ($d->q_type=="mcbox" || $d->q_type=="mlist") {
                    $item->$fieldname = implode(" ",$data[$fieldname]);
                } else if ($d->q_type=='cbox') {
                    $item->$fieldname = $data[$fieldname];
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
                    $other->$fieldname=$data[$fieldname."_other"];
                }
                if ($d->q_type=="mcbox" || $d->q_type=="mlist") {
                    $other->$fieldname=$data[$fieldname."_other"];
                }
                if ($d->cf_type != 'password') {
                    $app->setUserState('mpoll.poll'.$pollid.'.'.$fieldname, $item->$fieldname);
                    if ($other->$fieldname) {
                        $app->setUserState('mpoll.poll'.$pollid.'.'.$fieldname.'_other', $other->$fieldname);
                    }
                }
            }

            // Delete old results
            $query = $db->getQuery(true);
            $query->delete($db->quoteName('#__mpoll_results'));
            $conditions = [];
            $conditions[] = $db->quoteName('res_cm') . ' = '.$subid;
            if (count($attachmentFields)) $conditions[] = $db->quoteName('res_qid') . ' NOT IN (' . implode(',',$attachmentFields) . ')';
            $query->where($conditions);
            $db->setQuery($query);
            $db->execute();

            // Save results
            $flist = $this->getQuestions($pollid,false);
            foreach ($flist as $fl) {
                if ($fl->q_type != "attach") {
                    $fieldname = 'q_' . $fl->q_id;
                    $cmres = new stdClass();
                    $cmres->res_user = $userid;
                    $cmres->res_poll = $pollid;
                    $cmres->res_qid = $fl->q_id;
                    $cmres->res_ans = $db->escape($item->$fieldname);
                    $cmres->res_cm = $subid;
                    if (isset($other->$fieldname)) {
                        $cmres->res_ans_other = $db->escape($other->$fieldname);
                    } else {
                        $cmres->res_ans_other = "";
                    }
                    if (isset($otherAlt->$fieldname)) {
                        $cmres->res_ans_other_alt = $db->escape($otherAlt->$fieldname);
                    } else {
                        $cmres->res_ans_other_alt = "";
                    }
                    if (!$db->insertObject('#__mpoll_results', $cmres)) {
                        $this->setError("Error saving additional information.  Please resubmit.");
                        return false;
                    }
                }
            }

            // UPdate COmpleittion record
            $publihsed = $data['published'];
            $featured = $data['featured'];
            $start = $data['cm_start'];
            $end = $data['cm_end'];

            $query = $db->getQuery(true);
            $query->update("#__mpoll_completed");
            $query->set("published = ".$db->escape((int)$publihsed));
            $query->set("featured = ".$db->escape((int)$featured));
            $query->set('cm_start = "'.$db->escape($start).'"');
            $query->set('cm_end = "'.$db->escape($end).'"');
            $query->where("cm_id=".(int)$subid);
            $db->setQuery($query);
            if (!$db->execute()) {
                $this->setError($db->getErrorMsg());
                return false;
            }
        }
        catch (Exception $e)
        {
            $this->setError($e->getMessage());

            return false;
        }

        return true;
    }

    function getQuestions($pollid,$options=false)
    {
        $db = JFactory::getDBO();
        $app=Jfactory::getApplication();
        $jinput = JFactory::getApplication()->input;
        $query=$db->getQuery(true);
        $query->select('*');
        $query->from('#__mpoll_questions');
        $query->where('published > 0');
        $query->where('q_poll = '.$db->escape($pollid));
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
                }
            }

            //Load Question Params
            $registry = new JRegistry();
            $registry->loadString($q->params);
            $q->params = $registry->toObject();

        }
        return $qdata;
    }

    function getPayments($cmplid)
    {
        $db = JFactory::getDBO();
        $app=Jfactory::getApplication();
        $query=$db->getQuery(true);
        $query->select('*');
        $query->from('#__mpoll_payment');
        $query->where('pay_cm = '.$db->escape($cmplid));
        $db->setQuery( $query );
        $qdata = $db->loadObjectList();

        return $qdata;
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

    function getAvailableTemplates($pollId)
    {
        $db = JFactory::getDBO();
        $app=Jfactory::getApplication();
        $query=$db->getQuery(true);
        $query->select('*');
        $query->from('#__mpoll_email_templates');
        $query->where('tmpl_poll = '.$db->escape($pollId));
        $query->where('published >= 1');
        $db->setQuery( $query );
        $qdata = $db->loadObjectList();

        return $qdata;
    }

    function getTemplate($templateId)
    {
        $db = JFactory::getDBO();
        $app=Jfactory::getApplication();
        $query=$db->getQuery(true);
        $query->select('*');
        $query->from('#__mpoll_email_templates');
        $query->where('tmpl_id = '.$db->escape($templateId));
        $query->where('published >= 1');
        $db->setQuery( $query );
        $qdata = $db->loadObject();

        return $qdata;
    }

    public function hasEmailField($pollId) {
        $query = $this->_db->getQuery(true);
        $query->select('q_id');
        $query->from('#__mpoll_questions');
        $query->where('q_poll='.$pollId);
        $query->where('q_type="email"');
        $this->_db->setQuery( $query );
        $emailFields = $this->_db->loadResult();

        if ($emailFields) {
            return true;
        }
        return false;
    }

    function sendEmail($cmId,$templateId) {
        $db = JFactory::getDBO();
        $item = $this->getItem($cmId);
        $questions = $this->getQuestions($item->cm_poll,false);
        $template = $this->getTemplate($templateId);

        // Gather questions that have options
        $optfs = array();
        $moptfs = array();
        foreach ($questions as $d) {
            $fieldname = 'q_'.$d->q_id;
            if ($d->q_type == "multi" || $d->q_type == "dropdown") {
                $optfs[] = $fieldname;
            }
            if ($d->q_type == "mcbox" || $d->q_type == "mlist") {
                $moptfs[] = $fieldname;
            }
        }

        $optionsdata = array();

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

        $sendEmailField = 'q_'.$template->tmpl_email_to;
        $sendEmail = false;
        if (property_exists($item,$sendEmailField)) {
            $sendEmail = $item->$sendEmailField;
        }

        try {
            if ($sendEmail) {

                $completedid = base64_encode('cmplid='.$item->cm_id.'&id=' . $item->cm_pubid);
                $cmplurl = JUri::root().JRoute::link('site','index.php?option=com_mpoll&task=results&poll='.$item->cm_poll. '&cmpl=' . $completedid,false);
                $payurl = JUri::root().JRoute::link('site','index.php?option=com_mpoll&task=pay&poll='.$item->cm_poll. '&payment=' . $completedid,false);

                $confemail = $template->tmpl_content;
                $confemail = str_replace("{name}",$user->name,$confemail);
                $confemail = str_replace("{username}",$user->username,$confemail);
                $confemail = str_replace("{email}",$user->email,$confemail);
                $confemail = str_replace("{resid}",$cmId,$confemail);
                $confemail = str_replace("{resurl}",$cmplurl,$confemail);
                $confemail = str_replace("{payurl}",$payurl,$confemail);
                foreach ($questions as $d) {
                    $fieldname = 'q_'.$d->q_id;
                    if (property_exists($item,$fieldname)) {
                        if ($d->q_type=="attach") {

                        } else if (in_array($fieldname,$optfs)) {
                            $youropts="";
                            $youropts = $optionsdata[$item->$fieldname];
                            if ($other->$fieldname) $youropts .= ': '.$other->$fieldname;
                            $confemail = str_replace("{i".$d->q_id."}",$youropts,$confemail);
                        } else if (in_array($fieldname,$moptfs)) {
                            $youropts="";
                            $ans = $item->$fieldname;
                            foreach ($ans as $i) {
                                $youropts .= $optionsdata[$i].' ';
                            }
                            $confemail = str_replace("{i".$d->q_id."}",$youropts,$confemail);
                        } else {
                            $confemail = str_replace("{i" . $d->q_id . "}", $item->$fieldname, $confemail);
                        }
                    }
                }
                $mail = &JFactory::getMailer();
                $sent = $mail->sendMail($template->tmpl_from_email, $template->tmpl_from_name, $sendEmail, $template->tmpl_subject, $confemail, true);
            } else {
                return false;
            }
        }
        catch (Exception $e)
        {
            $this->setError($e->getMessage());

            return false;
        }

        return true;

    }

    public function getPayUrl($item) {
        $completedid = base64_encode('cmplid='.$item->cm_id.'&id=' . $item->cm_pubid);
        $payurl = JUri::root().JRoute::link('site','index.php?option=com_mpoll&task=pay&poll='.$item->cm_poll. '&payment=' . $completedid,false);
        return $payurl;
    }

}
