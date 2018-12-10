<?php
/**
 * 文章
 **/
namespace mobile\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class article_class extends mobileHomeControl{

	public function __construct() {
        parent::__construct();
    }
    
    public function indexOp() {
			$article_class_model	= model('article_class');
			$article_model	= model('article');
			$condition	= array();
			
			$article_class = $article_class_model->getClassList($condition);
			output_data(array('article_class' => $article_class));		
    }
}
