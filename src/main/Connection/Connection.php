<?php

namespace RendyRobbani\PHP\Connection;

use RendyRobbani\PHP\Component\Component;
use RendyRobbani\PHP\Configuration\Configuration;

#[Component]
final class Connection extends \PDO
{
	/**
	 * @param string $host
	 * @param string $database
	 * @param string $username
	 * @param string $password
	 */
	public function __construct(#[Configuration(key: "database.host")] public string     $host,
	                            #[Configuration(key: "database.database")] public string $database,
	                            #[Configuration(key: "database.username")] public string $username,
	                            #[Configuration(key: "database.password")] public string $password)
	{
		parent::__construct(
			dsn: "mysql:host=$this->host;dbname=$this->database",
			username: $this->username,
			password: $this->password,
		);
	}
}