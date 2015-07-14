<?php
namespace Caylof;

/**
 * 简单角色类
 *
 * @package Caylof
 * @author caylof
 */
class Role {

    /**
     * 角色名
     *
     * @var string
     */
    protected $roleName;

    /**
     * 角色拥有的权限值
     *
     * @var int
     */
    protected $authValue;

    /**
     * 父角色对象
     *
     * @var \Caylof\Role
     */
    protected $parentRole;

    /**
     * 构造函数
     *
     * @param string $roleName 角色名
     * @param \Caylof\Role $parentRole 父角色对象
     */
    public function __construct($roleName, Role $parentRole = null) {
        $this->roleName = $roleName;
        $this->authValue = 0;
        if ($parentRole) {
            $this->parentRole = $parentRole;
            $this->authValue = $parentRole->getAuthValue();
        }
    }

    /**
     * 给予某种权限
     *
     * @param \Caylof\Auth $auth
     * @return \Caylof\Role 以便链式操作
     */
    public function allow(Auth $auth) {
        $this->fetchAuthValueFromParent();
        $this->authValue |=  $auth->getAuthValue();
        return $this;
    }

    /**
     * 阻止某种权限
     *
     * @param \Caylof\Auth $auth
     * @return \Caylof\Role 以便链式操作
     */
    public function deny(Auth $auth) {
        $this->fetchAuthValueFromParent();
        $this->authValue &= ~$auth->getAuthValue();
        return $this;
    }

    /**
     * 检测是否拥有某种权限
     *
     * @param \Caylof\Auth $auth
     * @return bool
     */
    public function checkAuth(Auth $auth) {
        return (bool)($this->authValue & $auth->getAuthValue());
    }

    /**
     * 获取角色的权限值
     *
     * @return int
     */
    public function getAuthValue() {
        return $this->authValue;
    }

    /**
     * 获取父角色的权限
     */
    protected function fetchAuthValueFromParent() {
        if ($this->parentRole) {
            $this->authValue |= $this->parentRole->getAuthValue();
        }
    }
}
