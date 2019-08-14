<?php
/**
 * Copyright (c) 2014 Textmaster
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category    Addonline
 * @package     Textmaster_Textmaster
 * @copyright   Copyright (c) 2014 Textmaster
 * @author 	    Addonline (http://www.addonline.fr)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Textmaster_Textmaster
 *
 * @category    Addonline
 * @package     Textmaster_Textmaster
 * @copyright   Copyright (c) 2014 Textmaster
 * @author 	    Addonline (http://www.addonline.fr)
 */
class Textmaster_Textmaster_Helper_Data extends Mage_Core_Helper_Abstract
{

    private $_mot_exclus = array();

    private $_progression_score = array(
            'in_creation' => 0,
            'waiting_assignment' => 0.1,
            'in_progress' => 0.3,
            'in_review' => 0.7,
            'incomplete' => 0.,
            'completed' => 1,
            'paused' => 0.1,
            'quality_control' => 0.5,
            'copyscape' => 0.6,
            'counting_words' => 0
    );

    private $_api = false;

    #project_tm_completed callback lancer launch project

    public function countWord ($txt)
    {
        $txt = utf8_decode($txt);
        $txt = strip_tags($txt);
        $aMots = str_word_count($txt, 1, 
                utf8_decode(
                        'ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ'));
        foreach ($aMots as &$sMot) {
            if (in_array($sMot, $this->_mot_exclus)) {
                unset($sMot);
            }
        }
        return count($aMots);
    }

    public function arrayToStringForApi ($arr)
    {
        if (! is_array($arr))
            return $arr;
        $r = '';
        foreach ($arr as $k1 => $niv1) {
            if (! is_array($niv1))
                return '';
            foreach ($niv1 as $k2 => $niv2) {
                if (is_array($niv2)) {
                    foreach ($niv2 as $k3 => $niv3) {
                        $r .= ($k1 == 0 ? '' : '&') .
                                 urlencode($k1 . '[' . $k2 . ']' . '[' . $k3 .
                                 ']') . '=' . urlencode($niv3);
                    }
                } else {
                    $r .= urlencode(($k1 == 0 ? '' : '&') . $k1 . '[' . $k2 .
                             ']') . '=' . urlencode($niv2);
                }
            }
        }
        return $r;
    }
    public function arrayToStringForApi2 ($arr)
    {
        if (! is_array($arr))
            return $arr;
        $r = '';
        foreach ($arr as $k1 => $niv1) {
            if (! is_array($niv1))
                return '';
            foreach ($niv1 as $k2 => $niv2) {
                if (is_array($niv2)) {
                    foreach ($niv2 as $k3 => $niv3) {
                        $r .= ($k1 == 0 ? '' : '&') .
                        ($k1 . '[' . $k2 . ']' . '[' . $k3 .
                                ']') . '=' . ($niv3);
                    }
                } else {
                    $r .= (($k1 == 0 ? '' : '&') . $k1 . '[' . $k2 .
                            ']') . '=' . ($niv2);
                }
            }
        }
        return $r;
    }

    public function getCallbackUrl ()
    {
        // http://aomagento.addonline.devl/textmaster/callback/documentcount/
        return Mage::getUrl('textmaster/callback/documentcount');
    }
    
    public function getCallbackCompletedUrl ()
    {
        return Mage::getUrl('textmaster/callback/documentcomplete');
    }
    
    public function getCallbackUrlTmComplete()
    {
        return Mage::getUrl('textmaster/callback/projecttmcomplete');
    }

    public function getCallbackUrlInProgress ()
    {
        return Mage::getUrl('textmaster/callback/inprogress');
    }

    public function getProgression ($documents_statuses)
    {
        $nb_docs = 0;
        $progression = 0;
        
        foreach ($documents_statuses as $status => $doc) {
            $nb_docs += (int) $doc;
            if (isset($this->_progression_score[$status]) && (int) $doc)
                $progression += ((int) $doc) * $this->_progression_score[$status];
        }
        
        if ($nb_docs > 0)
            $progression = $progression / $nb_docs;
        return round($progression * 100, 2);
        
    }

    public function getApi ()
    {
        if ($this->_api === false) {
            $this->_api = Mage::getSingleton('textmaster/api');
        }
        return $this->_api;
    }

    /**
     * Return formated language code like : fr-fr or en-us
     * @param  $storeId Integer
     * @return string
     */
    public function getFormatedLangCode($storeId)
    {
        return strtolower(str_replace('_', '-', Mage::getStoreConfig('general/locale/code',$storeId)));
    }

    public function showProjectDiffWordCount($project){
        $totalWordCount = $project->getTotalWordCount();
        if(!$project->getTranslationMemory() or $totalWordCount == 0)
            return false;
        $diffWordCount = $project->getDiffWordCount();
        if((($diffWordCount*100)/$totalWordCount) >= 5)
            return true;
        return false;
    }
}
