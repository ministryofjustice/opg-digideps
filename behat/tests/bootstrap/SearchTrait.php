<?php

namespace DigidepsBehat;

trait SearchTrait
{
    /**
     * @Given I search in admin for a client with the term :searchTerm
     */
    public function searchAdminForClientWithTerm($searchTerm)
    {
        $this->visitAdminPath('/admin/client/search');
        $this->fillField('search_clients_q', $searchTerm);
        $this->pressButton('search_clients_search');
    }

    /**
     * @Given I search in admin for a deputy with the term :searchTerm
     */
    public function searchAdminForDeputyWithTerm($searchTerm)
    {
        $this->visitAdminPath('/admin');
        $this->fillField('admin_q', $searchTerm);
        $this->pressButton('admin_search');
    }

    /**
     * @Given I search in admin for a deputy with the term :searchTerm and filter role by :role
     */
    public function searchAdminForDeputyWithTermAndRoleFilter($searchTerm, $role)
    {
        $this->visitAdminPath('/admin');
        $this->fillField('admin_q', $searchTerm);
        $this->selectOption('admin_role_name', $role);
        $this->pressButton('admin_search');
    }

    /**
     * @Given I search in admin for a deputy with the term :searchTerm and include clients
     */
    public function searchAdminForDeputyWithTermIncludeClients($searchTerm)
    {
        $this->visitAdminPath('/admin');
        $this->fillField('admin_q', $searchTerm);
        $this->checkOption('Include clients');
        $this->pressButton('admin_search');
    }

    /**
     * @Given I search in admin for a deputy with the term :searchTerm and filter role by :role and include clients
     */
    public function searchAdminForDeputyWithTermAndRoleFilterIncludeClients($searchTerm, $role)
    {
        $this->visitAdminPath('/admin');
        $this->fillField('admin_q', $searchTerm);
        $this->selectOption('admin_role_name', $role);
        $this->checkOption('Include clients');
        $this->pressButton('admin_search');
    }

    /**
     * @Given I search for a client with the term :searchTerm
     */
    public function searchFrontendForClientWithTerm($searchTerm)
    {
        $this->visitPath('/org');
        $this->fillField('search', $searchTerm);
        $this->pressButton('search_submit');
    }
}
