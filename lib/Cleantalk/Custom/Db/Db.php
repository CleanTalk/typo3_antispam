<?php

namespace Cleantalk\Custom\Db;

class Db extends \Cleantalk\Common\Db\Db
{
    /**
     * Alternative constructor.
     * Initilize Database object and write it to property.
     * Set tables prefix.
     */
    protected function init()
    {
        $this->prefix = \Drupal::service('database')->tablePrefix();
    }

    /**
     * Safely replace place holders
     *
     * @param string $query
     * @param array  $vars
     *
     * @return $this
     */
    public function prepareAndExecute($query, $vars = array())
    {
        $query = str_replace('%s', '?', $query);
        $this->db_result = \Drupal::service('database')->query($query, $vars);
        return $this->db_result;
    }

    /**
     * Run any raw request
     *
     * @param $query
     *
     * @return bool|int Raw result
     */
    public function execute($query, $returnAffected = false)
    {
        if ( $returnAffected ) {
          $this->db_result = \Drupal::service('database')->query($query, [], ['return' => 2]);
        } else {
          $this->db_result = \Drupal::service('database')->query($query);
        }

        return $this->db_result;
    }

    /**
     * Fetchs first column from query.
     * May receive raw or prepared query.
     *
     * @param bool $query
     * @param bool $response_type
     *
     * @return array|object|void|null
     */
    public function fetch( $query = false, $response_type = false )
    {
      if (!$query) {
        $query = $this->getQuery();
      }
      $this->result = \Drupal::service('database')->query($query)->fetchAssoc();

      return $this->result;
    }

    /**
     * Fetchs all result from query.
     * May receive raw or prepared query.
     *
     * @param bool $query
     * @param bool $response_type
     *
     * @return array|object|null
     */
    public function fetchAll( $query = false, $response_type = false )
    {

        $this->db_result = \Drupal::service('database')->query($query);
        $this->result = array();

        while ($row = $this->db_result->fetchAssoc()){
            $this->result[] = $row;
        }
        return $this->result;
    }

    public function getAffectedRows() {
        if ( is_int($this->db_result) ) {
            return $this->db_result;
        }
    }
}
