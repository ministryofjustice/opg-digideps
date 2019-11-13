<?php

namespace DigidepsBehat;

trait SearchTrait
{
    /**
     * @Given I search for a client with the term :searchTerm
     */
    public function searchForClientWithTerm($searchTerm)
    {
        $this->visitAdminPath('/admin/client/search');
        $this->fillField('search_clients_q', $searchTerm);
        $this->pressButton('search_clients_search');
    }

    /**
     * @Given I search for a deputy with the term :searchTerm
     */
    public function searchForDeputyWithTerm($searchTerm)
    {
        $this->visitAdminPath('/admin');
        $this->fillField('admin_q', $searchTerm);
        $this->pressButton('admin_search');
    }

    /**
     * @Given I search for a deputy with the term :searchTerm and filter role by :role
     */
    public function searchForDeputyWithTermAndRoleFilter($searchTerm, $role)
    {
        $this->visitAdminPath('/admin');
        $this->fillField('admin_q', $searchTerm);
        $this->selectOption('admin_role_name', $role);
        $this->pressButton('admin_search');
    }

    /**
     * @Given I search for a deputy with the term :searchTerm and include clients
     */
    public function searchForDeputyWithTermIncludeClients($searchTerm)
    {
        $this->visitAdminPath('/admin');
        $this->fillField('admin_q', $searchTerm);
        $this->checkOption('Include clients');
        $this->pressButton('admin_search');
    }

    /**
     * @Given I search for a deputy with the term :searchTerm and filter role by :role and include clients
     */
    public function searchForDeputyWithTermAndRoleFilterIncludeClients($searchTerm, $role)
    {
        $this->visitAdminPath('/admin');
        $this->fillField('admin_q', $searchTerm);
        $this->selectOption('admin_role_name', $role);
        $this->checkOption('Include clients');
        $this->pressButton('admin_search');
    }
}
