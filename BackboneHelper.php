<?php
App::uses('AppHelper', 'View/Helper');

class BackboneHelper extends AppHelper {

/**
 * Directory inside webroot/js where our Backbone app is
 *
 * @var string
 */
	public $appDir = 'app';

/**
 * Helpers to use
 *
 * @var string
 */
	public $helpers = array('Html', 'Paginator');
	
	public function init($files = array(), $options = array('inline' => false)) {
		
		$viewVarsJson = json_encode(array(
			'paging' => $this->Paginator->params()
		));
		
		$backboneCore = array(
			'backbone/core/json2',
			'backbone/core/underscore',
			'backbone/core/mustache',
			'backbone/core/0.9.2/backbone'
		);
		
		$this->Html->script($backboneCore, $options);
		$this->Html->scriptBlock("var viewVars = $viewVarsJson;", $options);
		return $this->load($files, $options);
	}

/**
 * Method to load PSR-0-esque javascript files for backbone.
 *
 * Takes an array where the key is the class type Model, View, Collection, Controller
 * and the value is the script to load, or an array of scripts to load.
 *
 * @param array $files 
 * @param array $options options to pass to HtmlHelper::script() - default includes inline => false
 * @return Results of Html::script()
 * @author David Kullmann
 */
	public function load($files = array(), $options = array('inline' => false)) {
		
		$loadScripts = array();
		
		foreach ($files as $class => $file) {
			if (is_array($file)) {
				foreach ($file as $script) {
					$loadScripts[] = implode(DS, array($this->appDir, $class, $script));
				}
			} elseif(is_string($file)) {
				$loadScripts[] = implode(DS, array($this->appDir, $class, $file));
			}
		}
		
		return $this->Html->script($loadScripts, $options);
	}
/**
 * Convert a list of CakePHP records from Model::find() to backbone style records
 *
 * @param array $models Array of model records
 * @param string $alias Name of class to extract
 * @return string JS block to setup models
 * @author David Kullmann
 */
	public function bootstrap($models = array(), $alias = null, $options = array()) {
		
		$defaults = array(
			'inline' => true,
			'alias' => $alias,
			'merge' => false,
		);
		
		$options = array_merge($defaults, $options);

		$options['varName'] = !empty($options['varName']) ? $options['varName'] : $options['alias'];

		$toJson = $this->backbonify($models, $alias, $options);
				
		$toJson = json_encode($toJson);
				
		$alias = $options['alias'];
		$varName = $options['varName'];

		return $this->Html->scriptBlock("var $varName = $toJson;");
	}

/**
 * Convert models from CakePHP arrays to Backbone style
 *
 * @param array $models CakePHP find results
 * @param string $alias Alias to look for
 * @return array Array of models with alias removed
 * @author David Kullmann
 */
	public function backbonify($models = array(), $alias, $options = array()) {
		$converted = array();

		if (!empty($models)) {
			
			$key = key($models);

			if (is_integer($key)) {
				foreach ($models as $model) {
					$converted[] = $this->_extractModel($model, $alias, $options);
				}
			} else {
				$converted = $this->_extractModel($models, $alias, $options);
			}
		}
		
		return $converted;
	}
	
	protected function _extractModel($model = array(), $alias = null, $options = array()) {
		if (!$alias) {
			return $model;
		}
		if (!empty($options['merge'])) {
			if (is_string($options['merge'])) {
				$mergeModel = $options['merge'];
				return array_merge($model[$alias], $model[$mergeModel]);
			}
		}
		return $model[$alias];
	}
	
	// For backwards compatibility
	public function backboneifyRecords(&$records){
		$return = array();
		foreach($records as $id => $model) {
			$key = key($model);
			$data = $model[$key];
			$return[$key][] = $data;
		}
		return $return;
	}

/**
 * Return array of pagination vars for Backbone.PaginatedCollection
 *
 * @return array Array with total, per_page, and page keys
 * @author David Kullmann
 * @see https://github.com/GeReV/Backbone.PagedCollection
 */
	public function paginationVars() {
		extract($this->Paginator->params());
		return array(
			'total' => $count,
			'per_page' => $limit,
			'page' => $page
		);
	}

/**
 * Combine pagination vars, models, and viewvars
 *
 * @param string $modelName 
 * @param string $items 
 * @param string $viewVars 
 * @return void
 * @author David Kullmann
 */
	public function paginated($modelName, $items, $viewVars = array()) {
		$vars = $this->paginationVars();
		$vars['items'] = $this->backbonify($items, $modelName);
		$vars = array_merge($vars, $viewVars);
		return json_encode($vars);
	}
	
}