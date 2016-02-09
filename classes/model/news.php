<?php defined('SYSPATH') or die('No direct script access.');

class Model_News extends ORM_Base {

	protected $_sorting = array('public_date' => 'DESC');
	protected $_deleted_column = 'delete_bit';
	protected $_active_column = 'active';

	protected $_belongs_to = array(
		'category' => array(
			'model' => 'news_category',
			'foreign_key' => 'category_id',
		),
	);

	public function labels()
	{
		return array(
			'category_id' => 'Category',
			'title' => 'Title',
			'uri' => 'URI',
			'image' => 'Image',
			'announcement' => 'Announcement',
			'text' => 'Text',
			'active' => 'Active',
			'title_tag' => 'Title tag',
			'keywords_tag' => 'Keywords tag',
			'description_tag' => 'Desription tag',
			'public_date' => 'Date',
			'for_all' => 'For all sites',
		);
	}

	public function rules()
	{
		return array(
			'id' => array(
				array('digit'),
			),
			'site_id' => array(
				array('not_empty'),
				array('digit'),
			),
			'category_id' => array(
				array('not_empty'),
				array('digit'),
			),
			'title' => array(
				array('not_empty'),
				array('max_length', array(':value', 255)),
			),
			'uri' => array(
				array('min_length', array(':value', 2)),
				array('max_length', array(':value', 255)),
				array('alpha_dash'),
				array(array($this, 'check_uri')),
			),
			'image' => array(
				array('max_length', array(':value', 255)),
			),
			'title_tag' => array(
				array('max_length', array(':value', 255)),
			),
			'keywords_tag' => array(
				array('max_length', array(':value', 255)),
			),
			'description_tag' => array(
				array('max_length', array(':value', 255)),
			),
			'public_date' => array(
				array('date'),
			),
		);
	}

	public function filters()
	{
		return array(
			TRUE => array(
				array('trim'),
			),
			'title' => array(
				array('strip_tags'),
			),
			'active' => array(
				array(array($this, 'checkbox'))
			),
			'title_tag' => array(
				array('strip_tags'),
			),
			'keywords_tag' => array(
				array('strip_tags'),
			),
			'description_tag' => array(
				array('strip_tags'),
			),
			'for_all' => array(
				array(array($this, 'checkbox'))
			),
		);
	}

	public function apply_mode_filter()
	{
		parent::apply_mode_filter();

		if($this->_filter_mode == ORM_Base::FILTER_FRONTEND) {
			$this
				->where($this->_object_name.'.public_date', '<=', date('Y-m-d H:i:00'));
		}
	}
	
	public function check_uri($value)
	{
		if ( ! $this->active) {
			return TRUE;
		}
	
		$orm = clone $this;
		$orm->clear();
	
		if ($this->loaded()) {
			$orm
				->where('id', '!=', $this->id);
		}
	
		if ($this->for_all) {
			$orm
				->site_id(NULL);
		}
	
		$orm
			->where('category_id', '=', $this->category_id)
			->where('uri', '=', $this->uri)
			->where('active', '>', 0)
			->find();
	
		return ! $orm->loaded();
	}
}
