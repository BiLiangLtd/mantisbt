<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

require_api( 'authentication_api.php' );
require_api( 'constant_inc.php' );
require_api( 'config_api.php' );
require_api( 'helper_api.php' );
require_api( 'project_api.php' );

use Mantis\Exceptions\ClientException;

/**
 * A command that creates a project .
 *
 * Sample:
 * {
 *   "payload": {
 *     "name": "vboctor",
 *     "desc": "this is a desc",
 *     "status": "为开发中还是已经上线，",
       "view_status":"代表公开还是私有",
 *     "enabled": true,
 *     "inherit_global ": false,    # 是否继续全域分类。
 *     "file_path":""
 *   }
 * }
 */
class ProjectCreateCommand extends Command {
	/**
	 * @var string The name of the project being created.
	 */
	private $name;

	/**
	 * @var string project desc
	 */
	private $desc;

	/**
	 * @var int Whether the project is public
     */
	private $view_state;

	/**
	 * @var int project status
	 */
	private $status;

	/**
	 * @var boolean .Whether the project inherits global categories.
	 */
	private $inherit_global;

	/**
	 * @var boolean Whether the project is enabled.
	 */
	private $enabled;

    /**
     * @var string
     */
	private $file_path;

	/**
	 * Constructor
	 *
	 * @param array $p_data The command data.
	 */
	function __construct( array $p_data ) {
		parent::__construct( $p_data );
	}

	/**
	 * Validate the data.
	 */
	function validate() {
		# Ensure user has access level to create project
		if( !access_has_global_level( config_get_global( 'create_project_threshold' ) ) ) {
			throw new ClientException( 'Access denied to create project', ERROR_ACCESS_DENIED );
		}

		# status
        $t_status = array( 'name' => trim( $this->payload( 'status', '' )));
        $this->status = mci_get_project_status_id( $t_status );

        # view state
        $t_view_state = array( 'id' => trim( $this->payload( 'view_status', '' )));
        $this->view_state = mci_get_project_view_state_id($t_view_state);

		# title and project desc
		$this->name = trim( $this->payload( 'name', '' ) );
		$this->desc = trim( $this->payload( 'desc', '' ) );

		# inherit global and Enabled Flags
		$this->inherit_global = $this->payload( 'inherit_global', false );
		$this->enabled = $this->payload( 'enabled', true );

		$this->file_path = "";
	}

	/**
	 * Process the command.
	 *
	 * @returns array Command response
	 */
	protected function process() {
		# Need to send the user creation mail in the tracker language, not in the creating admin's language
		# Park the current language name until the user has been created
		lang_push( config_get_global( 'default_language' ) );

		$t_proj_id = project_create($this->name,
            $this->desc,
            $this->status,
            $this->view_state,
            $this->file_path,
            $this->enabled,
            $this->inherit_global
            );

		# set language back to user language
		lang_pop();

		return array( 'id' => $t_proj_id );
	}
}

