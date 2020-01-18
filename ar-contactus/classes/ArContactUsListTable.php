<?php

require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
ArContactUsLoader::loadModel('ArContactUsCallbackModel');

class ArContactUsListTable extends WP_List_Table
{
    protected $pageSize = 20;
    public function __construct() {
        parent::__construct(array(
            'singular' => 'arcontactus-request',     // Singular name of the listed records.
            'plural'   => 'arcontactus-requests',    // Plural name of the listed records.
            'ajax'     => false,       // Does this table support ajax?
            'per_page' => $this->pageSize
        ) );
    }
    
    public function get_columns() {
        $columns = array(
            'cb'       => '<input type="checkbox" />',
            'id_user'    => _x('User','Column label', AR_CONTACTUS_TEXT_DOMAIN),
            'phone'   => _x('Phone','Column label', AR_CONTACTUS_TEXT_DOMAIN),
            'created_at' => _x('Created at','Column label', AR_CONTACTUS_TEXT_DOMAIN),
            'status' => _x('Status','Column label', AR_CONTACTUS_TEXT_DOMAIN),
        );
        return $columns;
    }
    
    function get_sortable_columns() {
        $sortable_columns = array(
            'phone' => array('phone', false),
            'created_at' => array('created_at', true),
            'status' => array('status', false)
        );
        return $sortable_columns;
    }
    
    public function prepare_items() {        
        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array( $columns, $hidden, $sortable );
        
        $this->process_bulk_action();
        
        $data = $this->prepare_data();
        
        $q = ArContactUsCallbackModel::find();
        if (isset($_REQUEST['status'])){
            $q->where(array('status' => (int)$_REQUEST['status']));
        }
        
        $total_items = $q->count();
        
        $this->items = $data;

        $this->set_pagination_args(array(
            'total_items' => $total_items,                     
            'per_page'    => $this->pageSize,                        
            'total_pages' => ceil($total_items / $this->pageSize)
        ));
    }
    
    protected function column_default($item, $column_name)
    {
        switch($column_name) {
            case 'id':
            case 'phone':
            case 'created_at':
                return $item[$column_name];
            case 'id_user':
                return $this->getUserName($item);
            case 'status':
                return $this->getStatus($item);
            default:
                return print_r($item, true);
        }
    }
    
    protected function getStatus($item)
    {
        return ArContactUsAdmin::render('admin/partials/status.php', array(
            'item' => $item
        ));
    }


    protected function getUserName($item)
    {
        if ($item['id_user']){
            $user = get_user_by('id', $item['id_user']);
            return $user->data->user_nicename;
        }
        return '-';
    }
    
    protected function deleteBulkAction($id){
        ArContactUsCallbackModel::deleteAll(array('id' => $id));
    }
    
    protected function statusBulkAction($id, $status)
    {
        ArContactUsCallbackModel::updateAll(array(
            'status' => (int)$status,
            'updated_at' => date('Y-m-d H:i:s')
        ), array(
            'id' => $id
        ));
    }


    /**
     * Handle bulk actions.
     *
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     *
     * @see $this->prepare_items()
     */
    protected function process_bulk_action() {
        $ids = array();
        // Detect when a bulk action is being triggered.
        if(in_array($this->current_action(), array('delete', 'done', 'new', 'ignore'))){
            if(isset($_REQUEST['arcontactus-request'])){
                $delete_ids =  (array)$_REQUEST['arcontactus-request'];
                if(is_array($delete_ids) && count($delete_ids)>0){
                    foreach($delete_ids as $id){
                        switch ($this->current_action()){
                            case 'delete':
                                $this->deleteBulkAction($id);
                                break;
                            case 'new':
                                $this->statusBulkAction($id, 0);
                                break;
                            case 'done':
                                $this->statusBulkAction($id, 1);
                                break;
                            case 'ignore':
                                $this->statusBulkAction($id, 2);
                                break;
                        }
                        $ids[] = $id;
                    }
                }
            }
        }
    }
    
    function get_bulk_actions() {
        $actions = array(
            'delete' => _x( 'Delete', 'List table bulk action', AR_CONTACTUS_TEXT_DOMAIN),
            'new' => _x( 'Mark as new', 'List table bulk action', AR_CONTACTUS_TEXT_DOMAIN),
            'done' => _x( 'Mark as done', 'List table bulk action', AR_CONTACTUS_TEXT_DOMAIN),
            'ignore' => _x( 'Mark as ignored', 'List table bulk action', AR_CONTACTUS_TEXT_DOMAIN),
        );

        return $actions;
    }


    /**
     * Get value for checkbox column.
     *
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     *
     * @param object $item A singular item (one full row's worth of data).
     * @return string Text to be placed inside the column <td>.
     */
    protected function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            $this->_args['singular'],
            $item['id']
        );
    }
    
    public function print_column_headers( $with_id = true ) {
            list($columns, $hidden, $sortable) = $this->get_column_info();

            if (strpos($_SERVER['REQUEST_URI'], 'admin-ajax.php') !== false){
                $current_url = set_url_scheme($_POST['location']);
            }else{
                $current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
            }
            $current_url = remove_query_arg( 'paged', $current_url );

            if ( isset( $_GET['orderby'] ) ) {
                    $current_orderby = $_GET['orderby'];
            } else {
                    $current_orderby = '';
            }

            if ( isset( $_GET['order'] ) && 'desc' === $_GET['order'] ) {
                    $current_order = 'desc';
            } else {
                    $current_order = 'asc';
            }

            if ( ! empty( $columns['cb'] ) ) {
                    static $cb_counter = 1;
                    $columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __('Select All') . '</label>'
                            . '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" />';
                    $cb_counter++;
            }

            foreach ( $columns as $column_key => $column_display_name ) {
                    $class = array( 'manage-column', "column-$column_key" );

                    if ( in_array( $column_key, $hidden ) ) {
                            $class[] = 'hidden';
                    }

                    if ( 'cb' === $column_key )
                            $class[] = 'check-column';
                    elseif ( in_array( $column_key, array( 'posts', 'comments', 'links' ) ) )
                            $class[] = 'num';

                    if ( isset( $sortable[$column_key] ) ) {
                            list( $orderby, $desc_first ) = $sortable[$column_key];

                            if ( $current_orderby === $orderby ) {
                                    $order = 'asc' === $current_order ? 'desc' : 'asc';
                                    $class[] = 'sorted';
                                    $class[] = $current_order;
                            } else {
                                    $order = $desc_first ? 'desc' : 'asc';
                                    $class[] = 'sortable';
                                    $class[] = $desc_first ? 'asc' : 'desc';
                            }

                            $column_display_name = '<a href="' . esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
                    }

                    $tag = ( 'cb' === $column_key ) ? 'td' : 'th';
                    $scope = ( 'th' === $tag ) ? 'scope="col"' : '';
                    $id = $with_id ? "id='$column_key'" : '';

                    if ( !empty( $class ) )
                            $class = "class='" . join( ' ', $class ) . "'";

                    echo "<$tag $scope $id $class>$column_display_name</$tag>";
            }
    }
    
    protected function prepare_data()
    {
        $page = $this->get_pagenum() - 1;
        $orderby = (isset($_REQUEST['orderby']))? $_REQUEST['orderby'] : '';
        $order = (isset($_REQUEST['order']))? $_REQUEST['order'] : '';
        
        $perPage = $this->pageSize;
        $data = array();
        $q = ArContactUsCallbackModel::find()->offset($page * $perPage)->limit($perPage);
        if ($order && $orderby){
            $q->orderBy("`{$orderby}` {$order}");
        }else{
            $q->orderBy('`created_at` DESC');
        }
        if (isset($_REQUEST['status'])){
            $q->where(array('status' => (int)$_REQUEST['status']));
        }
        $models = $q->all();
        if(is_array($models) && count($models)>0) {
            foreach ($models as $model){
                $item = array(
                    'id' => $model->id,
                    'phone' => $model->phone,
                    'id_user' => $model->id_user,
                    'created_at' => $model->created_at,
                    'status' => $model->status
                );
                $data[] = $item;
            }
        }
        return $data;
    }
    
    
    function pagination( $which ) {
        if ( empty( $this->_pagination_args ) ) {
                return;
        }

        $total_items = $this->_pagination_args['total_items'];
        $total_pages = $this->_pagination_args['total_pages'];
        $infinite_scroll = false;
        if ( isset( $this->_pagination_args['infinite_scroll'] ) ) {
                $infinite_scroll = $this->_pagination_args['infinite_scroll'];
        }

        if ( 'top' === $which && $total_pages > 1 && method_exists($this->screen, 'render_screen_reader_content')) {
                $this->screen->render_screen_reader_content( 'heading_pagination' );
        }

        $output = '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

        $current = $this->get_pagenum();
        $removable_query_args = wp_removable_query_args();
        
        if (strpos($_SERVER['REQUEST_URI'], 'admin-ajax.php') !== false){
            $current_url = set_url_scheme($_POST['location']);
        }else{
            $current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
        }
        $current_url = remove_query_arg( $removable_query_args, $current_url );

        $page_links = array();

        $total_pages_before = '<span class="paging-input">';
        $total_pages_after  = '</span></span>';

        $disable_first = $disable_last = $disable_prev = $disable_next = false;

        if ( $current == 1 ) {
                $disable_first = true;
                $disable_prev = true;
        }
        if ( $current == 2 ) {
                $disable_first = true;
        }
        if ( $current == $total_pages ) {
                $disable_last = true;
                $disable_next = true;
        }
        if ( $current == $total_pages - 1 ) {
                $disable_last = true;
        }

        if ( $disable_first ) {
                $page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&laquo;</span>';
        } else {
                $page_links[] = sprintf( "<a class='first-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                        esc_url( remove_query_arg( 'paged', $current_url ) ),
                        __( 'First page' ),
                        '&laquo;'
                );
        }

        if ( $disable_prev ) {
                $page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&lsaquo;</span>';
        } else {
                $page_links[] = sprintf( "<a class='prev-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                        esc_url( add_query_arg( 'paged', max( 1, $current-1 ), $current_url ) ),
                        __( 'Previous page' ),
                        '&lsaquo;'
                );
        }

        if ( 'bottom' === $which ) {
                $html_current_page  = $current;
                $total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page' ) . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
        } else {
                $html_current_page = sprintf( "%s<input class='current-page' id='current-page-selector' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
                        '<label for="current-page-selector" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
                        $current,
                        strlen( $total_pages )
                );
        }
        $html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
        $page_links[] = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . $total_pages_after;

        if ( $disable_next ) {
                $page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&rsaquo;</span>';
        } else {
                $page_links[] = sprintf( "<a class='next-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                        esc_url( add_query_arg( 'paged', min( $total_pages, $current+1 ), $current_url ) ),
                        __( 'Next page' ),
                        '&rsaquo;'
                );
        }

        if ( $disable_last ) {
                $page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&raquo;</span>';
        } else {
                $page_links[] = sprintf( "<a class='last-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                        esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
                        __( 'Last page' ),
                        '&raquo;'
                );
        }

        $pagination_links_class = 'pagination-links';
        if ( ! empty( $infinite_scroll ) ) {
                $pagination_links_class .= ' hide-if-js';
        }
        $output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

        if ( $total_pages ) {
                $page_class = $total_pages < 2 ? ' one-page' : '';
        } else {
                $page_class = ' no-pages';
        }
        $this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

        echo $this->_pagination;
    }
}
