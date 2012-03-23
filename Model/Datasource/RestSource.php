<?php
class RestSource extends DataSource {
	public $description = 'Rest Source';

	/**
	* Execute a custom query against the REST server
	*
	* $model->get('custom_method',)
	* $model->post('custom_method', array())
	*
	* @param string $method The HTTP verb to execute (get, post, pull, delete)
	* @param array $pass	The raw configuration
	* @param Model $model	The model that triggered the call
	* @return mixed
	*/
	public function query($method, $pass, Model $model) {
		if (empty($pass)) {
			throw new Exception('Missing information about the HTTP request');
		}

		$config = $pass[0];
		if (!is_array($config)) {
			$config = array('action' => $config);
		}

		if (empty($config['action'])) {
			throw new Exception('Missing action key');
		}

		$url = sprintf('/%s/%s', $model->remoteResource, $config['action']);
		$cu = new Curl($this->getBaseUrl() . $url);
		$this->applyConfiguration($cu);

		$data = null;
		if (in_array($method, array('put', 'post')) && isset($pass[1])) {
			$data = $pass[1];
		}

		return call_user_func(array($cu, $method), $data);
	}

	/**
	* Execute a HTTP POST request against a REST resource
	*
	* @param Model $model	The model that is executing the save()
	* @param array $fields	A list of fields that needs to be saved
	* @param array $values	A list of values that need to be saved
	* @return mixed
	*/
	public function create(Model $model, $fields = null, $values = null) {
		$data = array_combine($fields, $values);
		if (empty($data['id']) && $this->read($model, array('conditions' => array('id' => $data['id']))))  {
			$url	= sprintf('/%s', $model->remoteResource);
			$method = 'post';
		} else {
			$url = sprintf('/%s/%s', $model->remoteResource, $data['id']);
			$method = 'put';
		}

		$cu = new Curl($this->getBaseUrl() . $url);
		$this->applyConfiguration($cu);

		return call_user_func(array($cu, $method), $data);
	}

	/**
	* Execute a GET request against a REST resource
	*
	* @param Model $model		The model that is executing find() / read()
	* @param array $queryData	The conditions for the find - currently we only support "id" => $value
	* @return mixed
	*/
	public function read(Model $model, $queryData = array()) {
		$url = $this->config['host'] . DS . $model->remoteResource;

		if (isset($queryData['action'])) {
			$url .= DS . $queryData['action'];
		}

		if (!empty($queryData['conditions']['id'])) {
			$url .= DS . $queryData['conditions']['id'];
			unset($queryData['conditions']['id']);
		}

 		$url = trim($url, DS) . '.' . $this->config['format'];

		if (!empty($queryData['limit'])) {
			$queryData['conditions']['limit'] = $queryData['limit'];
		}

		if (!empty($queryData['offset'])) {
			$queryData['conditions']['offset'] = $queryData['offset'];
		}

		if (!empty($queryData['order'])) {
			$queryData['conditions']['order'] = $queryData['order'];
		}

		if (!empty($queryData['page'])) {
			$queryData['conditions']['page'] = $queryData['page'];
		}

		if (!empty($queryData['conditions'])) {
			$url .= '?' . http_build_query($queryData['conditions']);
		}

		$cu = new \Nodes\Curl($url);
		$this->applyConfiguration($cu);

		$data = $cu->get()->getResponseBody();

		if (empty($data['success'])) {
			return array();
		}

		return $data['data'];
	}

	/**
	* Execute a PUT request against a REST resource
	*
	* @param Model $model		The model that is executing the save()
	* @param array $fields		A list of fields that needs to be saved
	* @param array $values		A list of values that need to be saved
	* @param array $conditions	Update conditions - currently not used
	* @return mixed
	*/
	public function update(Model $model, $fields = array(), $values = null, $conditions = null) {
		$data	= array_combine($fields, $values);
		$url	= sprintf('/%s', $model->remoteResource);
		$cu		= new Curl($this->getBaseUrl() . $url);
		$this->applyConfiguration($cu);

		return $cu->put($data);
	}

	/**
	* Execute a DELETE request against a REST resource
	*
	* @param Model $model	The model that is executing the delete()
	* @param mixed $id		The resource ID to delete
	* @return mixed
	*/
	public function delete(Model $model, $id = null) {
		$url	= sprintf('/%s/%s', $model->remoteResource, $id);
		$cu		= new Curl($this->getBaseUrl() . $url);
		$this->applyConfiguration($cu);

		return $cu->delete();
	}

	/**
	* Build the baseURL based on configuration options
	*  - protocol	string	Can be HTTP or HTTPS (default)
	*  - hostname	string	The hostname of the application server
	*  - admin		boolean If the remote URL is within an admin routing
	*
	* @return string
	*/
	public function getBaseUrl() {
		return $this->config['host'];
	}

	/**
	 * Caches/returns cached results for child instances
	 *
	 * @param mixed $data
	 * @return array Array of sources available in this datasource.
	 */
	public function listSources($data = null) {
		return true;
	}

	/**
	* Apply some custom confiuration to our cURL object
	* - Set the Platform-Token HTTP header for remote authentication
	* - Set the
	*
	* @param Curl $cu	The cURL object we want to apply configuration for
	* @return void
	*/
	public function applyConfiguration(\Nodes\Curl $cu) {
		//$cu->setOption('headers' );
	}
}