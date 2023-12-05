<?php
abstract class BaseUser
{
    protected $id;
    protected $name;
    protected $email;
    protected $password;

    // Конструктор для установки основных свойств пользователя
    public function __construct($id, $name, $email, $password)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
    }

    // Абстрактный метод, который должен быть реализован в дочерних классах
    abstract public function getRole();

    // Другие общие методы для пользователей могут быть добавлены здесь
}

class RegularUser extends BaseUser
{
    public function getRole()
    {
        return 'regular';
    }

    // Дополнительные методы для обычных пользователей
}

class AdminUser extends BaseUser
{
    public function getRole()
    {
        return 'admin';
    }

    // Дополнительные методы для администраторов
}

// Пример использования
$regularUser = new RegularUser(1, 'John Doe', 'john@example.com', 'hashed_password');
$adminUser = new AdminUser(2, 'Admin', 'admin@example.com', 'hashed_password');

echo $regularUser->getRole();  // Выведет 'regular'
echo $adminUser->getRole();    // Выведет 'admin'
?>
