<?php

declare(strict_types=1);

namespace Core;

use Core\Exception\HttpNotFoundException;
use PDO;
use PDOException;

class Database
{
    private $connection;

    private $statement;

    public function __construct($config, $username = 'root', $password = '')
    {
        try {
            $dsn = 'mysql:'.http_build_query($config, '', ';');
            $this->connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (\PDOException $e) {
            throw new PDOException("Fail to connect to database: {$e->getMessage()} ");
        }
    }

    /**
     * Executa uma consulta SQL com parâmetros.
     *
     * @param  string  $query  A consulta SQL a ser executada.
     * @param  array  $params  Os parâmetros a serem vinculados à consulta.
     * @return $this Retorna a instância atual da classe Database.
     */
    public function queryBuilder($query, $params = []): self
    {
        $this->statement = $this->connection->prepare($query);
        $this->statement->execute($params);

        return $this;
    }

    /**
     * Executa uma consulta SQL sem parâmetros.
     *
     * @return mixed Retorna o resultado da consulta.
     */
    public function find()
    {
        return $this->statement->fetch();
    }

    /**
     * Executa uma consulta SQL e retorna todos os resultados.
     *
     * @return array Retorna todos os resultados da consulta.
     */
    public function findAll(): array
    {
        return $this->statement->fetchAll();
    }

    /**
     * Executa uma consulta SQL e retorna o resultado ou lança uma exceção se não houver resultado.
     *
     * @return mixed Retorna o resultado da consulta ou lança uma exceção HttpNotFoundException se não houver resultado.
     */
    public function findOrFail()
    {
        $result = $this->find();
        if (! $result) {
            throw new HttpNotFoundException;
        }

        return $result;
    }

    /**
     * Inicia uma transação.
     *
     * @return bool Retorna true em caso de sucesso ou false em caso de falha.
     */
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Confirma uma transação.
     *
     * @return bool Retorna true em caso de sucesso ou false em caso de falha.
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    /**
     * Reverte uma transação.
     *
     * @return bool Retorna true em caso de sucesso ou false em caso de falha.
     */
    public function rollBack(): bool
    {
        return $this->connection->rollBack();
    }

    /**
     * Verifica se uma transação está ativa.
     *
     * @return bool Retorna true se uma transação estiver ativa, false caso contrário.
     */
    public function inTransaction(): bool
    {
        return $this->connection->inTransaction();
    }
}
