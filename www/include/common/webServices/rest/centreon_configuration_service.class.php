<?php
/*
 * Copyright 2005-2015 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 * SVN : $URL$
 * SVN : $Id$
 *
 */

class CentreonConfigurationService
{
    /**
     *
     * @var type 
     */
    protected $pearDB;
    
    /**
     * 
     */
    public function __construct()
    {
        $this->pearDB = new CentreonDB();
    }
    
    /**
     * 
     * @param array $args
     * @return array
     */
    public function getList($args = array())
    {
        global $centreon;
        
        $userId = $centreon->user->user_id;
        $isAdmin = $centreon->user->admin;
        
        /* Get ACL if user is not admin */
        if (!$isAdmin) {
            $acl = new CentreonACL($userId, $isAdmin);
            $aclGroups = $acl->getAccessGroupsString();
        }
        
        // Check for select2 'q' argument
        if (false === isset($args['q'])) {
            $q = '';
        } else {
            $q = $args['q'];
        }
        
        $queryService = "SELECT DISTINCT s.service_description, s.service_id, h.host_name, h.host_id "
            . "FROM host h, service s, host_service_relation hsr "
            . 'WHERE hsr.host_host_id = h.host_id '
            . "AND hsr.service_service_id = s.service_id "
            . "AND h.host_register = '1' AND s.service_register = '1' "
            . "ORDER BY h.host_name";
        
        $DBRESULT = $this->pearDB->query($queryService);
        
        $serviceList = array();
        while ($data = $DBRESULT->fetchRow()) {
            $serviceCompleteName = $data['host_name'] . ' - ' . $data['service_description'];
            $serviceCompleteId = $data['host_id'] . '-' . $data['service_id'];
            
            $serviceList[] = array('id' => htmlentities($serviceCompleteId), 'text' => htmlentities($serviceCompleteName));
        }
        
        return $serviceList;
    }
    
    /**
     * 
     * @param type $args
     * @return array
     */
    public function getDefaultEscalationValues($args = array())
    {
        $defaultValues = array();
        
        // Check for select2 'q' argument
        if (false === isset($args['q'])) {
            $q = '';
        } else {
            $q = $args['q'];
        }
        
        $queryService = "SELECT distinct host_host_id, host_name, service_service_id, service_description
            FROM service s, escalation_service_relation esr, host h
            WHERE s.service_id = esr.service_service_id
            AND esr.host_host_id = h.host_id
            AND h.host_register = '1'
            AND esr.escalation_esc_id = " . $q;
        $DBRESULT = $this->pearDB->query($queryService);
        
        while ($data = $DBRESULT->fetchRow()) {
            $serviceCompleteName = $data['host_name'] . ' - ' . $data['service_description'];
            $serviceCompleteId = $data['host_host_id'] . '-' . $data['service_service_id'];
            
            $defaultValues[] = array('id' => htmlentities($serviceCompleteId), 'text' => htmlentities($serviceCompleteName));
        }
        
        return $defaultValues;
    }
}