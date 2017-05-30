<?php namespace Zara4\LazyMySql;

use Illuminate\Database\MySqlConnection;


class Connection extends MySqlConnection {

  /** @var bool $readConnected */
  protected $readConnected;

  /** @var bool $writeConnected */
  protected $writeConnected;


  /** @var array $readConfig */
  protected $readConfig;

  /** @var array $writeConfig */
  protected $writeConfig;


  /** @var \Zara4\LazyMySql\Connector|null $readConnector */
  protected $readConnector;

  /** @var \Zara4\LazyMySql\Connector|null $writeConnector */
  protected $writeConnector;


  // --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- ---


  /**
   * @param array $config
   */
  public function __construct(array $config) {

    // Establish write connector
    $this->writeConnected   = false;
    $this->writeConfig      = Config::getWriteConfig($config);
    $this->writeConnector   = new Connector();

    // Establish read connector (if provided)
    if (Config::hasReadConfig($config)) {
      $this->readConnected  = false;
      $this->readConfig     = Config::getReadConfig($config);
      $this->readConnector  = new Connector();
    }

    // --- --- --- --- --- --- ---

    // First we will setup the default properties. We keep track of the DB
    // name we are connected to since it is needed when some reflective
    // type commands are run such as checking whether a table exists.
    $this->database = $this->writeConfig['database'];

    $this->tablePrefix = $config['prefix'];

    $this->config = $config;


    // We need to initialize a query grammar and the query post processors
    // which are both very important parts of the database abstractions
    // so we initialize these to their default values while starting.
    $this->useDefaultQueryGrammar();

    $this->useDefaultPostProcessor();

  }


  /**
   *
   */
  protected function ensureReadConnected() {
    if ($this->readConnector) {

      if (!$this->readConnected) {
        $this->readConnected = true;
        $this->setReadPdo($this->readConnector->connect($this->readConfig));
      }

    } else {
      $this->ensureWriteConnected();
    }
  }


  /**
   *
   */
  protected function ensureWriteConnected() {
    if (!$this->writeConnected) {
      $this->writeConnected = true;
      $this->setPdo($this->writeConnector->connect($this->writeConfig));
    }
  }


  // --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- ---


  /**
   * Get the current PDO connection.
   *
   * @return \PDO
   */
  public function getPdo() {
    $this->ensureWriteConnected();
    return parent::getPdo();
  }


  /**
   * Get the current PDO connection used for reading.
   *
   * @return \PDO
   */
  public function getReadPdo() {
    $this->ensureReadConnected();
    return parent::getReadPdo();
  }


  /**
   * Start a new database transaction.
   *
   * @return void
   *
   * @throws \Exception
   */
  public function beginTransaction() {
    self::ensureWriteConnected();
    parent::beginTransaction();
  }


  /**
   * Commit the active database transaction.
   *
   * @return void
   */
  public function commit() {
    self::ensureWriteConnected();
    parent::commit();
  }


  /**
   * Rollback the active database transaction.
   *
   * @return void
   */
  public function rollBack() {
    self::ensureWriteConnected();
    parent::rollBack();
  }


  /**
   * Reconnect to the database if a PDO connection is missing.
   *
   * @return void
   */
  protected function reconnectIfMissingConnection() {
    $readShouldBeConnectedButIsnt  = $this->readConnected && is_null($this->getReadPdo());
    $writeShouldBeConnectedButIsnt = $this->writeConnected && is_null($this->getPdo());

    if ($readShouldBeConnectedButIsnt || $writeShouldBeConnectedButIsnt) {
      $this->reconnect();
    }
  }


}