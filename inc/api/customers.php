<?php

/**
 * Customers API for Metorik.
 */
class Metorik_Helper_API_Customers extends WC_REST_Posts_Controller
{
    public $namespace = 'wc/v1';
    public $rest_base = 'customers';

    public $WC_REST_Customers_Controller;

    /**
     * Constructor.
     */
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'customers_ids_route']);
        add_action('rest_api_init', [$this, 'customers_updated_route']);
        add_action('rest_api_init', [$this, 'customers_roles_route']);

        /*
         * Temporarily override WC core customers/customer endpoints to fix bug with customer last order
         * Don't need to do in 2.7+ as just total spent / order count and we do that with meta filter
         * This only happens during imports & for 2.6.
         *
         * Will be depreciated and removed in the near future.
         */
        if (get_option('metorik_importing_currently', false) && version_compare(WC()->version, '2.7.0', '<')) {
            $this->WC_REST_Customers_Controller = new WC_REST_Customers_Controller();
            add_action('rest_api_init', [$this, 'customers_route']);
            add_action('rest_api_init', [$this, 'single_customer_route']);
        }
    }

    /**
     * Customers IDs route definition.
     */
    public function customers_ids_route()
    {
        register_rest_route($this->namespace, '/customers/ids/', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$this, 'customers_ids_callback'],
            'permission_callback' => [$this, 'get_items_permissions_check'],
        ]);
    }

    /**
     * Customers updated route definition.
     */
    public function customers_updated_route()
    {
        register_rest_route($this->namespace, '/customers/updated/', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$this, 'customers_updated_callback'],
            'permission_callback' => [$this, 'get_items_permissions_check'],
        ]);
    }

    /**
     * Customers roles route definition.
     */
    public function customers_roles_route()
    {
        register_rest_route($this->namespace, '/customers/roles/', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$this, 'customers_roles_callback'],
            'permission_callback' => [$this, 'get_items_permissions_check'],
        ]);
    }

    /**
     * Customers route definition.
     * This overrides the WC core customers endpoint, so we need
     * to include the CREATE method from WC core too
     * so that this doesn't break it if it's
     * used by the store during imports.
     */
    public function customers_route()
    {
        register_rest_route($this->namespace, '/customers/', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'customers_api_callback'],
                'permission_callback' => [$this->WC_REST_Customers_Controller, 'get_items_permissions_check'],
                'args'                => $this->WC_REST_Customers_Controller->get_collection_params(),
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this->WC_REST_Customers_Controller, 'create_item'],
                'permission_callback' => [$this->WC_REST_Customers_Controller, 'create_item_permissions_check'],
                'args'                => array_merge($this->WC_REST_Customers_Controller->get_endpoint_args_for_item_schema(WP_REST_Server::CREATABLE), [
                    'email' => [
                        'required' => true,
                    ],
                    'username' => [
                        'required' => 'no' === get_option('woocommerce_registration_generate_username', 'yes'),
                    ],
                    'password' => [
                        'required' => 'no' === get_option('woocommerce_registration_generate_password', 'no'),
                    ],
                ]),
            ],
            'schema' => [$this->WC_REST_Customers_Controller, 'get_public_item_schema'],
        ], true);
    }

    /**
     * Customer (single) route definition.
     * This overrides the WC core customers endpoint, so we need
     * to include the CREATE method from WC core too
     * so that this doesn't break it if it's
     * used by the store during imports.
     */
    public function single_customer_route()
    {
        register_rest_route($this->namespace, '/customers/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_item'],
                'permission_callback' => [$this->WC_REST_Customers_Controller, 'get_item_permissions_check'],
                'args'                => [
                    'context' => $this->get_context_param(['default' => 'view']),
                ],
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [$this->WC_REST_Customers_Controller, 'update_item'],
                'permission_callback' => [$this->WC_REST_Customers_Controller, 'update_item_permissions_check'],
                'args'                => $this->get_endpoint_args_for_item_schema(WP_REST_Server::EDITABLE),
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [$this->WC_REST_Customers_Controller, 'delete_item'],
                'permission_callback' => [$this->WC_REST_Customers_Controller, 'delete_item_permissions_check'],
                'args'                => [
                    'force' => [
                        'default'     => false,
                        'description' => __('Required to be true, as resource does not support trashing.', 'woocommerce'),
                    ],
                    'reassign' => [],
                ],
            ],
            'schema' => [$this->WC_REST_Customers_Controller, 'get_public_item_schema'],
        ], true);
    }

    /**
     * Callback for the Customer IDs API endpoint.
     * Will likely be depreciated in a future version in favour of the customers updated endpoint.
     * When that happens, we'll likely need to group results by user ID.
     *
     * @return WP_Error|array
     */
    public function customers_ids_callback()
    {
        global $wpdb;

        /**
         * Get users where their capability/role includes customer.
         */
        $customers = $wpdb->get_results(
            "
				SELECT user_id
				FROM $wpdb->usermeta
				WHERE meta_key = 'wp_capabilities' 
					AND meta_value LIKE '%customer%'
			"
        );

        /*
         * No customers.
         */
        if (!$customers || !is_array($customers)) {
            return false;
        }

        /**
         * Just get IDs.
         */
        $ids = [];
        foreach ($customers as $customer) {
            if (isset($customer->user_id)) {
                $ids[] = intval($customer->user_id);
            }
        }

        /**
         * Prepare response.
         */
        $data = [
            'count' => count($ids),
            'ids'   => $ids,
        ];

        /**
         * Response.
         */
        $response = rest_ensure_response($data);
        $response->set_status(200);

        return $response;
    }

    /**
     * Callback for the Customers updated API endpoint.
     * Later this will likely replace the IDs endpoint completely as it gets depreciated.
     */
    public function customers_updated_callback($request)
    {
        global $wpdb;

        /**
         * Check days set and use default if not.
         */
        $days = 30;
        if (isset($request['days'])) {
            $days = intval($request['days']);
        }

        /**
         * Check hours set and use default if not.
         */
        $hours = 0;
        if (isset($request['hours'])) {
            $hours = intval($request['hours']);
        }

        // How many days back?
        $time = strtotime('- '.$days.' days');

        // if have hours, subtract
        if ($hours) {
            $time = $time - (60 * 60 * $hours);
        }

        // limit/offset
        $limit = 200000;
        $offset = 0;

        if (isset($request['limit'])) {
            $limit = intval($request['limit']);
        }

        if (isset($request['offset'])) {
            $offset = intval($request['offset']);
        }

        /*
         * Get customers where the last update is greater than x days ago.
         * The date needs to be a timestring.
         *
         * Different query depending on if this is multisite or snot.
         */

        // Multisite
        if (is_multisite()) {
            // Cap column - prefix includes site ID
            $cap_column = $wpdb->prefix.'capabilities';

            // Query
            $customers = $wpdb->get_results($wpdb->prepare(
                "
					SELECT 
						a.user_id as id,
						b.meta_value as last_updated
					FROM $wpdb->usermeta AS a
					INNER JOIN $wpdb->usermeta AS b
						ON b.user_id = a.user_id
						AND b.meta_key = 'last_update'
						AND b.meta_value > %d
					WHERE a.meta_key = %s 
					LIMIT %d, %d
				", [
                    $time,
                    $cap_column,
                    $offset,
                    $limit,
                ]
            ));
        } else {
            // Not multisite
            $customers = $wpdb->get_results($wpdb->prepare(
                "
					SELECT 
						user_id as id,
						meta_value as last_updated
					FROM $wpdb->usermeta
					WHERE meta_key = 'last_update' 
						AND meta_value > %d
					LIMIT %d, %d
				", [
                    $time,
                    $offset,
                    $limit,
                ]
            ));
        }

        /**
         * Prepare response.
         */
        $data = [
            'customers' => $customers,
        ];

        /**
         * Response.
         */
        $response = rest_ensure_response($data);
        $response->set_status(200);

        return $response;
    }

    /**
     * Callback for the Customers roles API endpoint.
     */
    public function customers_roles_callback($request)
    {
        // we need this to use the get_editable_roles() function
        require get_home_path().'/wp-admin/includes/user.php';

        /**
         * Format roles (we don't need all caps).
         */
        $editable_roles = get_editable_roles();
        $roles = [];
        foreach ($editable_roles as $key => $editable_role) {
            $roles[$key] = $editable_role['name'];
        }

        /**
         * Prepare response.
         */
        $data = [
            'roles' => $roles,
        ];

        /**
         * Response.
         */
        $response = rest_ensure_response($data);
        $response->set_status(200);

        return $response;
    }

    /**
     * Get a single customer.
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_item($request)
    {
        $id = (int) $request['id'];
        $customer = get_userdata($id);

        if (empty($id) || empty($customer->ID)) {
            return new WP_Error('woocommerce_rest_invalid_id', __('Invalid resource id.', 'woocommerce'), ['status' => 404]);
        }

        $customer = $this->prepare_item_for_response($customer, $request);
        $response = rest_ensure_response($customer);

        return $response;
    }

    /**
     * Callback for the customers API endpoint.
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_Error|array
     */
    public function customers_api_callback($request)
    {
        $prepared_args = [];
        $prepared_args['exclude'] = $request['exclude'];
        $prepared_args['include'] = $request['include'];
        $prepared_args['order'] = $request['order'];
        $prepared_args['number'] = $request['per_page'];
        if (!empty($request['offset'])) {
            $prepared_args['offset'] = $request['offset'];
        } else {
            $prepared_args['offset'] = ($request['page'] - 1) * $prepared_args['number'];
        }
        $orderby_possibles = [
            'id'              => 'ID',
            'include'         => 'include',
            'name'            => 'display_name',
            'registered_date' => 'registered',
        ];
        $prepared_args['orderby'] = $orderby_possibles[$request['orderby']];
        $prepared_args['search'] = $request['search'];

        if ('' !== $prepared_args['search']) {
            $prepared_args['search'] = '*'.$prepared_args['search'].'*';
        }

        // Filter by email.
        if (!empty($request['email'])) {
            $prepared_args['search'] = $request['email'];
            $prepared_args['search_columns'] = ['user_email'];
        }

        // Filter by role.
        if ('all' !== $request['role']) {
            $prepared_args['role'] = $request['role'];
        }

        /**
         * Filter arguments, before passing to WP_User_Query, when querying users via the REST API.
         *
         * @see https://developer.wordpress.org/reference/classes/wp_user_query/
         *
         * @param array           $prepared_args Array of arguments for WP_User_Query.
         * @param WP_REST_Request $request       The current request.
         */
        $prepared_args = apply_filters('woocommerce_rest_customer_query', $prepared_args, $request);

        $query = new WP_User_Query($prepared_args);

        $users = [];
        foreach ($query->results as $user) {
            $data = $this->prepare_item_for_response($user, $request);
            $users[] = $this->prepare_response_for_collection($data);
        }

        $response = rest_ensure_response($users);

        // Store pagation values for headers then unset for count query.
        $per_page = (int) $prepared_args['number'];
        $page = ceil((((int) $prepared_args['offset']) / $per_page) + 1);

        $prepared_args['fields'] = 'ID';

        $total_users = $query->get_total();
        if ($total_users < 1) {
            // Out-of-bounds, run the query again without LIMIT for total count.
            unset($prepared_args['number']);
            unset($prepared_args['offset']);
            $count_query = new WP_User_Query($prepared_args);
            $total_users = $count_query->get_total();
        }
        $response->header('X-WP-Total', (int) $total_users);
        $max_pages = ceil($total_users / $per_page);
        $response->header('X-WP-TotalPages', (int) $max_pages);

        $base = add_query_arg($request->get_query_params(), rest_url(sprintf('/%s/%s', $this->namespace, $this->rest_base)));
        if ($page > 1) {
            $prev_page = $page - 1;
            if ($prev_page > $max_pages) {
                $prev_page = $max_pages;
            }
            $prev_link = add_query_arg('page', $prev_page, $base);
            $response->link_header('prev', $prev_link);
        }
        if ($max_pages > $page) {
            $next_page = $page + 1;
            $next_link = add_query_arg('page', $next_page, $base);
            $response->link_header('next', $next_link);
        }

        return $response;
    }

    /**
     * Prepare a single customer output for response.
     *
     * @param WP_User         $customer Customer object.
     * @param WP_REST_Request $request  Request object.
     *
     * @return WP_REST_Response $response Response data.
     */
    public function prepare_item_for_response($customer, $request)
    {
        $data = [
            'id'               => $customer->ID,
            'date_created'     => wc_rest_prepare_date_response($customer->user_registered),
            'date_modified'    => $customer->last_update ? wc_rest_prepare_date_response(date('Y-m-d H:i:s', $customer->last_update)) : null,
            'email'            => $customer->user_email,
            'first_name'       => $customer->first_name,
            'last_name'        => $customer->last_name,
            'username'         => $customer->user_login,
            'orders_count'     => wc_get_customer_order_count($customer->ID),
            'total_spent'      => wc_get_customer_total_spent($customer->ID),
            'avatar_url'       => wc_get_customer_avatar_url($customer->customer_email),
            'billing'          => [
                'first_name' => $customer->billing_first_name,
                'last_name'  => $customer->billing_last_name,
                'company'    => $customer->billing_company,
                'address_1'  => $customer->billing_address_1,
                'address_2'  => $customer->billing_address_2,
                'city'       => $customer->billing_city,
                'state'      => $customer->billing_state,
                'postcode'   => $customer->billing_postcode,
                'country'    => $customer->billing_country,
                'email'      => $customer->billing_email,
                'phone'      => $customer->billing_phone,
            ],
            'shipping'         => [
                'first_name' => $customer->shipping_first_name,
                'last_name'  => $customer->shipping_last_name,
                'company'    => $customer->shipping_company,
                'address_1'  => $customer->shipping_address_1,
                'address_2'  => $customer->shipping_address_2,
                'city'       => $customer->shipping_city,
                'state'      => $customer->shipping_state,
                'postcode'   => $customer->shipping_postcode,
                'country'    => $customer->shipping_country,
            ],
        ];

        $context = !empty($request['context']) ? $request['context'] : 'view';
        $data = $this->add_additional_fields_to_object($data, $request);
        $data = $this->filter_response_by_context($data, $context);

        // Wrap the data in a response object.
        $response = rest_ensure_response($data);

        $response->add_links($this->prepare_links($customer, $request));

        /*
         * Filter customer data returned from the REST API.
         *
         * @param WP_REST_Response $response  The response object.
         * @param WP_User          $customer  User object used to create response.
         * @param WP_REST_Request  $request   Request object.
         */
        return apply_filters('woocommerce_rest_prepare_customer', $response, $customer, $request);
    }

    /**
     * Check whether a given request has permission to read customers.
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_Error|bool
     */
    public function get_items_permissions_check($request)
    {
        if (!wc_rest_check_user_permissions('read')) {
            return new WP_Error('woocommerce_rest_cannot_view', __('Sorry, you cannot list resources.', 'woocommerce'), ['status' => rest_authorization_required_code()]);
        }

        return true;
    }
}

new Metorik_Helper_API_Customers();
