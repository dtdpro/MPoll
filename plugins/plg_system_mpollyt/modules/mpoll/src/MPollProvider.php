<?php

use YooTheme\Database;

class MPollProvider
{
    public static function getResults($pollid,$limit) {
        $db = JFactory::getDBO();

        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('#__mpoll_completed AS r');
        $query->where('r.published = 1');
        $query->where('r.cm_poll = '.$db->escape($pollid));
        $query->order('r.cm_time DESC');
        $db->setQuery($query,0,$limit);
        $data = $db->loadObjectList();

        //Get Results
        foreach ($data as &$d) {
            $resQuery = $db->getQuery(true);

            $resQuery->select('*');
            $resQuery->from('#__mpoll_results AS r');
            $resQuery->where('res_cm = '.$db->escape($d->cm_id));
            $db->setQuery($resQuery);
            $cmd = $db->loadObjectList();

            foreach ($cmd as $c) {
                $fn='q_'.$c->res_qid;
                $fno='q_'.$c->res_qid.'_other';
                $fna='q_'.$c->res_qid.'_other_alt';
                $d->$fn=$c->res_ans;
                $d->$fno=$c->res_ans_other;
                $d->$fna=$c->res_ans_other_alt;
            }
        }

        return $data;
    }

    public static function getQuestions() {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query->select('q.*,p.poll_name');
        $query->from("#__mpoll_questions as q");
        $query->join('RIGHT', $db->quoteName('#__mpoll_polls', 'p') . ' ON ' . $db->quoteName('q.q_poll') . ' = ' . $db->quoteName('p.poll_id'));
        $query->where('q.published = 1');
        $query->where("q.q_type NOT IN ('captcha','message','header')");
        $query->order('q.q_poll ASC, q.ordering ASC');
        $db->setQuery($query);
        $items = $db->loadObjectList();
        foreach ($items as &$q) {
            //Load options
            if ($q->q_type == "multi" || $q->q_type == "mcbox" || $q->q_type == "dropdown" || $q->q_type == "mlist") {
                $qo=$db->getQuery(true);
                $qo->select('opt_txt, opt_id, opt_disabled, opt_correct, opt_color, opt_other, opt_selectable, opt_blank');
                $qo->from('#__mpoll_questions_opts');
                $qo->where('opt_qid = '.$q->q_id);
                $qo->where('published > 0');
                $qo->order('ordering ASC');
                $db->setQuery($qo);
                $queriedOptions = $db->loadAssocList();
                $options = [];
                foreach ($queriedOptions as $option) {
                    $id = $option['opt_id'];
                    $options[$id] = $option;
                }
                $q->options = $options;
            }
        }

        return $items;
    }


    public static function pollList() {
        $db	= JFactory::getDbo();
        $qc=$db->getQuery(true);
        $qc->select('poll_id,poll_name');
        $qc->from('#__mpoll_polls');
        $qc->where('published >= 1');
        $qc->order('poll_name ASC');
        $db->setQuery($qc);
        return $db->loadObjectList();
    }

    function sortByField($array, $field) {
        $length = count($array);
        for ($i = 0; $i < $length; $i++) {
            for ($j = $i + 1; $j < $length; $j++) {
                if ($array[$i]->$field > $array[$j]->$field) {
                    $temp = $array[$i];
                    $array[$i] = $array[$j];
                    $array[$j] = $temp;
                }
            }
        }
        return $array;
    }

}
