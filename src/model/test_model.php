<?php
/**
 * ����ʹ�õ�����ģ��
 * @author Tongle Xu <xutongle@gmail.com> 2013-2-23
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id$
 */
class test_model extends Model {

	public function __construct() {
		$this->setting = 'default';//Ҫ���ص����ݿ�����
		$this->table_name = 'test';//Ҫ���������ݱ�
		parent::__construct ();
	}
}