<?php

namespace App\Datatables\CenterUser;

trait DataTablePermissionTrait
{
    /**
     * Check if user has permission
     *
     * @param string $permission
     * @return bool
     */
    protected function hasPermission(string $permission): bool
    {
        if (!auth('center_user')->check()) {
            return false;
        }
        
        return auth('center_user')->user()->can($permission, 'center_api');
    }

    /**
     * Get permission name for CREATE action
     *
     * @return string
     */
    protected function getCreatePermission(): string
    {
        return 'CREATE_' . strtoupper($this->plural);
    }

    /**
     * Get permission name for UPDATE action
     *
     * @return string
     */
    protected function getUpdatePermission(): string
    {
        return 'UPDATE_' . strtoupper($this->plural);
    }

    /**
     * Get permission name for DELETE action
     *
     * @return string
     */
    protected function getDeletePermission(): string
    {
        return 'DELETE_' . strtoupper($this->plural);
    }

    /**
     * Check if user can create
     *
     * @return bool
     */
    protected function canCreate(): bool
    {
        return $this->hasPermission($this->getCreatePermission());
    }

    /**
     * Check if user can update
     *
     * @return bool
     */
    protected function canUpdate(): bool
    {
        return $this->hasPermission($this->getUpdatePermission());
    }

    /**
     * Check if user can delete
     *
     * @return bool
     */
    protected function canDelete(): bool
    {
        return $this->hasPermission($this->getDeletePermission());
    }
}

