<?php

class Pagination
{
    /**
     * Biến config chứa tất cả các cấu hình
     *
     * @var array
     */
    private $config = [
        'total' => 0, // tổng số mẩu tin
        'limit' => 0, // số mẩu tin trên một trang
        'full' => true, // true nếu hiện full số page, flase nếu không muốn hiện false
        'querystring' => 'page' // GET id nhận page
    ];

    public function __construct($config = [])
{
    if ((isset($config['limit']) && $config['limit'] < 0) || (isset($config['total']) && $config['total'] < 0)) {
        throw new moodle_exception('error_limit_total', 'local_th_log_email');
    }
    if (!isset($config['querystring'])) {
        $config['querystring'] = 'page';
    }
    $this->config = $config;
}


    private function gettotalPage()
    {
        return ceil($this->config['total'] / $this->config['limit']);
    }

    private function getCurrentPage()
    {
        if (isset($_GET[$this->config['querystring']]) && (int)$_GET[$this->config['querystring']] >= 1) {
            if ((int)$_GET[$this->config['querystring']] > $this->gettotalPage()) {
                return (int)$this->gettotalPage();
            } else {
                return (int)$_GET[$this->config['querystring']];
            }

        } else {
            return 1;
        }
    }

    private function getPrePage()
    {
        if ($this->getCurrentPage() === 1) {
            return '<li><span>' . get_string('first_page', 'local_th_log_email') . '</span></li>
                    <li><span>' . get_string('previous_page', 'local_th_log_email') . '</span></li>';
        } else {
            return '<li><a href="' . $_SERVER['PHP_SELF'] . '?' . $this->config['querystring'] . '=' . 1 . '" >' . get_string('first_page', 'local_th_log_email') . '</a></li>
                    <li><a href="' . $_SERVER['PHP_SELF'] . '?' . $this->config['querystring'] . '=' . ($this->getCurrentPage() - 1) . '" >' . get_string('previous_page', 'local_th_log_email') . '</a></li>';
        }
    }

    private function getPrePage1()
    {
        if (isset($_GET['page'])){
            $page = (int)addslashes($_GET['page']);
        } else {
            $page = 1;
        }
        if ($page === 1) {
            return '<li><span>' . get_string('first_page', 'local_th_log_email') . '</span></li>
                    <li><span>' . get_string('previous_page', 'local_th_log_email') . '</span></li>';
        } else {
            return '<li><a href="' . $_SERVER['PHP_SELF'] . '?' . $this->config['querystring'] . '=' . 1 . '" >' . get_string('first_page', 'local_th_log_email') . '</a></li>
                    <li><a href="' . $_SERVER['PHP_SELF'] . '?' . $this->config['querystring'] . '=' . ($page - 1) . '" >' . get_string('previous_page', 'local_th_log_email') . '</a></li>';
        }
    }

    private function getNextPage()
    {
        if ($this->getCurrentPage() >= $this->gettotalPage()) {
            return '<li><span>' . get_string('next_page', 'local_th_log_email') . '</span></li>
                    <li><span>' . get_string('last_page', 'local_th_log_email') . '</span></li>';
        } else {
            return '<li><a href="' . $_SERVER['PHP_SELF'] . '?' . $this->config['querystring'] . '=' . ($this->getCurrentPage() + 1) . '" >' . get_string('next_page', 'local_th_log_email') . '</a></li>
                    <li><a href="' . $_SERVER['PHP_SELF'] . '?' . $this->config['querystring'] . '=' . ($this->gettotalPage()) . '" >' . get_string('last_page', 'local_th_log_email') . '</a></li>';
        }
    }

    private function getNextPage1()
    {
        if (isset($_GET['page'])){
            $page = (int)addslashes($_GET['page']);
        } else {
            $page = 1;
        }
        
        if ($page >= $this->gettotalPage()) {
            return '<li><span>' . get_string('next_page', 'local_th_log_email') . '</span></li>
                    <li><span>' . get_string('last_page', 'local_th_log_email') . '</span></li>';
        } else {
            return '<li><a href="' . $_SERVER['PHP_SELF'] . '?' . $this->config['querystring'] . '=' . ($page + 1) . '" >' . get_string('next_page', 'local_th_log_email') . '</a></li>
                    <li><a href="' . $_SERVER['PHP_SELF'] . '?' . $this->config['querystring'] . '=' . ($this->gettotalPage()) . '" >' . get_string('last_page', 'local_th_log_email') . '</a></li>';
        }
    }

    public function getPagination()
    {
        $data = '';
        if (isset($this->config['full']) && $this->config['full'] === false) {
            $data .= ($this->getCurrentPage() - 3) > 1 ? '<li class = "pagination_hidden">...</li>' : '';
            for ($i = ($this->getCurrentPage() - 3) > 0 ? ($this->getCurrentPage() - 3) : 1; $i <= (($this->getCurrentPage() + 3) > $this->gettotalPage() ? $this->gettotalPage() : ($this->getCurrentPage() + 3)); $i++) {
                if ($i === $this->getCurrentPage()) {
                    $data .= '<li><a href="#" class="active">' . $i . '</a></li>';
                } else {
                    $data .= '<li><a href="' . $_SERVER['PHP_SELF'] . '?' . $this->config['querystring'] . '=' . $i . '" >' . $i . '</a></li>';
                }
            }

            $data .= ($this->getCurrentPage() + 3) < $this->gettotalPage() ? '<li class = "pagination_hidden">...</li>' : '';
        } else {
            for ($i = 1; $i <= $this->gettotalPage(); $i++) {
                if ($i === $this->getCurrentPage()) {
                    $data .= '<li><a href="#" class="active">' . $i . '</a></li>';
                } else {
                    $data .= '<li><a href="' . $_SERVER['PHP_SELF'] . '?' . $this->config['querystring'] . '=' . $i . '" >' . $i . '</a></li>';
                }
            }
        }
        return '<ul>' . $this->getPrePage() . $data . $this->getNextPage() . '</ul>';
    }



    public function getPagination1()
    {
        $data = '';
        if (isset($_GET['page'])){
            $page = (int)addslashes($_GET['page']);
        } else {
            $page = 1;
        }
        if (isset($this->config['full']) && $this->config['full'] === false) {
            $data .= ($page - 3) > 1 ? '<li class = "pagination_hidden">...</li>' : '';
            for ($i = ($page - 3) > 0 ? ($page - 3) : 1; $i <= (($page + 3) > $this->gettotalPage() ? $this->gettotalPage() : ($page + 3)); $i++) {
                if ($i === $page) {
                    $data .= '<li><a href="#" class="active">' . $i . '</a></li>';
                } else {
                    $data .= '<li><a href="' . $_SERVER['PHP_SELF'] . '?' . $this->config['querystring'] . '=' . $i . '" >' . $i . '</a></li>';
                }
            }

            $data .= ($page + 3) < $this->gettotalPage() ? '<li class = "pagination_hidden">...</li>' : '';
        } else {
            for ($i = 1; $i <= $this->gettotalPage(); $i++) {
                if ($i === $page) {
                    $data .= '<li><a href="#" class="active">' . $i . '</a></li>';
                } else {
                    $data .= '<li><a href="' . $_SERVER['PHP_SELF'] . '?' . $this->config['querystring'] . '=' . $i . '" >' . $i . '</a></li>';
                }
            }
        }
        return '<ul>' . $this->getPrePage1() . $data . $this->getNextPage1() . '</ul>';
    }
}