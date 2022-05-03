<?php
class mysqlo{
    public $connect; // Переменная объекта соединения
    function __construct(array $data){ // Конструктор класса
        try {
            $this->connect = new PDO(
                "mysql:dbname={$data['dbnm']};host={$data['addr']};charset=utf8;" ,
                $data['user'],
                $data['pass']
            ); // Создание подключения
            $this->connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) { // При ошибке
            trigger_error('Ошибка подключения Базы Данных: ' . $exception->getMessage(), E_USER_ERROR); // Пишем её
        }
    }
    function query(string $sql, array $parameters, bool $all = false){ // Функция выполнения запроса к MySql
        try {
            $statement = $this->connect->prepare($sql); // Подготавлеваем запрос
            $newParameters = array_combine(
                array_map(function($k){ return ':'.$k; }, array_keys($parameters))
                , $parameters
            );
            $statement->execute($newParameters); // Выполняем запрос
            if($all){ // Если передано, что передавать все полученные данные, то
                return $statement->fetchAll(PDO::FETCH_ASSOC); // Передаём все даннные
            }else{ // Иначе
                return $statement->fetch(PDO::FETCH_ASSOC); // Передаём первые попавшиеся
            }
        } catch (Exception $exception) { // При ошибке
            trigger_error( 'Ошибка запроса: ' . $exception->getMessage(), E_USER_ERROR); // Пишем её
        }
    }
    function transaction(array $query_list){ // Функция удобной транзакции
        try {
            $this->connect->beginTransaction(); // Объявляем начало транзакции
            foreach($query_list as $sql => $parameters){ // Проходимся по каждому запросу
                $statement = $this->connect->prepare($sql); // Подготавлеваем запрос
                $newParameters = array_combine(
                    array_map(function($k){ return ':'.$k; }, array_keys($parameters))
                    , $parameters
                );
                $statement->execute($newParameters); //Выполняем запрос
            }
            $this->connect->commit(); // Сохраняем изменения
        } catch (Exception $exception) { // Если что-то пошло не так
            $this->connect->rollBack(); // Откатываем (отменяем) изменения
            trigger_error('Ошибка транзакции: ' . $exception->getMessage(), E_USER_ERROR); // И выводим ошибку
        }
    }
}
