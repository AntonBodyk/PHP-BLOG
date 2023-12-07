<?php
abstract class BaseUser
{
    protected $id;
    protected $name;
    protected $email;
    protected $password;

    public function __construct($id, $name, $email, $password)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
    }

    abstract public function getRole();

}

class RegularUser extends BaseUser
{
    public function getRole()
    {
        return 'regular';
    }
}

class AdminUser extends BaseUser
{
    public function getRole()
    {
        return 'admin';
    }
}


$regularUser = new RegularUser(1, 'John Doe', 'john@example.com', 'hashed_password');
$adminUser = new AdminUser(2, 'Admin', 'admin@example.com', 'hashed_password');

echo $regularUser->getRole();
echo $adminUser->getRole();
?>
