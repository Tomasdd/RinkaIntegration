<?php
require_once dirname(__FILE__) . '/../../vendor/RinkaAsi/src/RinkaAsi/RinkaAsi.php';

//Vendors
require_once 'vendors/pagination.php';

require_once 'RinkaIntegrationException.php';
require_once 'RinkaIntegrationFactory.php';
require_once 'bridge/RinkaIntegrationRinkaAsiFactory.php';
require_once 'bridge/RinkaIntegrationRinkaAsiCache.php';

class RinkaIntegration {

    /**
     * @var RinkaIntegrationAsiBridge
     */
    protected $bridge = null;

    /**
     * @var RinkaIntegrationConfig
     */
    protected $config = null;

    /**
     * @var RinkaIntegrationFactory
     */
    protected $factory = null;

    /**
     * @var RinkaIntegrationCacheInterface
     */
    protected $cache = null;


    /**
     *
     * @param RinkaIntegrationAsiBridge         $bridge
     * @param RinkaIntegrationConfig            $config
     * @param RinkaIntegrationCacheInterface    $cache
     * @param RinkaIntegrationFactory           $factory [Optional]	if not specified, default one is created
     */
    public function __construct(
        RinkaIntegrationAsiBridge $bridge,
        RinkaIntegrationConfig $config,
        RinkaIntegrationCacheInterface $cache,
        RinkaIntegrationFactory $factory = null
    ) {
        $this->bridge  = $bridge;
        $this->config  = $config;
        $this->cache   = $cache;

        if ($factory === null) {
            $this->factory = new RinkaIntegrationFactory($this->config);
        } else {
            $this->factory = $factory;
        }
    }

    /**
     * @param array $config
     * @return RinkaIntegration
     */
    public static function createFromConfig(array $config) {
        $factory = new RinkaIntegrationFactory(new RinkaIntegrationConfig(
            isset($config['RinkaIntegration']) ? $config['RinkaIntegration'] : array()
        ));
        $cache = $factory->getCache();
        $rinkaAsiCache = new RinkaIntegrationRinkaAsiCache($cache);

        $asi = new RinkaAsi(
            new RinkaAsiConfig(
                isset($config['RinkaAsi']) ? $config['RinkaAsi'] : array()
            ),
            new RinkaIntegrationRinkaAsiFactory($rinkaAsiCache)
        );

        return new RinkaIntegration(
            $factory->getAsiBridge($asi),
            $factory->getConfig(),
            $cache,
            $factory
        );
    }

    /**
     * Handles announcement insertion page:
     * 1) shows category tree
     * 2) shows insertion form after category selection
     * 3) inserts announcement and redirects to success message or shows insertion form again on errors
     * 4) shows success message
     *
     * Returns fully rendered HTML code.
     * Takes $get (usually $_GET), $post (usually $_POST) and $files (usually $_FILES)
     * parameters to recognise needed action.
     *
     * @param array 	$get   [Optional]	if not defined, takes $_GET
     * @param array 	$post  [Optional]	if not defined, takes $_POST
     * @param array 	$files [Optional]	if not defined, takes $_FILES
     * @return string
     */
    public function handleInsert($get = null, $post = null, $files = null) {
        $get   = ($get   === null) ? $_GET   : $get;
        $post  = ($post  === null) ? $_POST  : $post;
        $files = ($files === null) ? $_FILES : $files;

        $url              = $this->config->getUrl('insertAnnouncement');
        $categoryPath     = (isset($get['category']) && !empty($get['category']))
            ? urldecode($get['category'])
            : null;

        // Handle 3)
        if (!empty($categoryPath) && count($post) > 0) {
            try {
                if (($announcementId = $this->bridge->insertAnnouncement($categoryPath, $post, $files['images']))) {
                    return $this->factory->getTemplate('redirect')->render(array(
                        'url' => $url . (strpos($url, '?') === false ? '?' : '&') . 'success_id=' . ((int) $announcementId),
                    ));
                }
            } catch (RinkaAsiException $e) {
                if ($e->getCode() != RinkaAsiException::E_CONNECTION) {
                    throw $e;
                }
                header('Location: '. $this->config->getUrl('base'));
                exit;
            }
        }
        // Handle 4)
        if (isset($get['success_id'])) {
            return $this->factory->getTemplate('success')->render(array(
                'announcementId' => (int) $get['success_id'],
            ));
        }

        try {
            $categoryTree = $this->bridge->getCategoryTree($url, $categoryPath);
        } catch (RinkaAsiException $e) {
            if ($e->getCode() != RinkaAsiException::E_CONNECTION) {
                throw $e;
            }
            header('Location: '. $this->config->getUrl('base'));
            exit;
        }

        // Handle 2)
        if (!empty($categoryPath)) {
            try {
                $categoryTitles = $this->bridge->getCategoryTitles(explode('/', $categoryPath));
                $insertForm = $this->bridge->getInsertForm(explode('/', $categoryPath));
            } catch (RinkaAsiException $e) {
                if ($e->getCode() != RinkaAsiException::E_CONNECTION) {
                    throw $e;
                }
                header('Location: '. $this->config->getUrl('base'));
                exit;
            }
            return $this->factory->getTemplate('insertForm')->render(array(
                'categoryTree'     => $categoryTree,
                'categoryTitle'	   => $categoryTitles,
                'insertForm'       => $insertForm,
                'validationErrors' => array_flip($this->bridge->validationErrors),
                'postData'         => $post,
                'getData'          => $get,
            ));
        }

        // Handle 1)
        return $this->factory->getTemplate('insertFormChooseCategory')->render(array(
            'categoryTree' => $categoryTree,
        ));
    }

    /**
     * Returns formatted announcements list HTML code or announcement details HTML code.
     * If parameter 'id' is set, displays announcement details, if not - announcement list.
     * Takes into account filtering, ordering and paging.
     *
     * @uses RinkaIntegrationAsiBridge          for getting announcements from remote server
     * @param array $get [Optional]		if not defined, takes $_GET
     * @return string				HTML code to be inserted into page
     */
    public function getAnnouncementsList($get = null, RinkaAsiFilter $defaultFilter = null) {
        $get = ($get === null) ? $_GET : $get;

        if (isset($get['id'])) {
            $filter = new RinkaAsiFilter();
            $filter->setId($get['id']);

            try {
                $announcement = $this->bridge->getAnnouncements($filter);
            } catch (RinkaAsiException $e) {
                if ($e->getCode() != RinkaAsiException::E_CONNECTION) {
                    throw $e;
                }
                header('Location: '. $this->config->getUrl('base'));
            }
            if (empty($announcement['ads'])) {
                return $this->factory->getTemplate('announcementNotFound')->render();
            }

            return $this->factory->getTemplate('announcementDetails')->render(array(
            	'announcement' => current($announcement['ads']),
                'bridge' => $this->bridge,
            ));
        } else {
            if ($defaultFilter === null) {
                $filter = new RinkaAsiFilter();
            } else {
                $filter = $defaultFilter;
            }
        }

        $categoryPath     = (isset($get['category']) && !empty($get['category']))
            ? urldecode($get['category'])
            : null;

        // Pagination
        $page  = isset($get['page'])
            ? (intval($get['page'])  === 0 ? 1  : intval($get['page']))
            : 1;
        $limit = isset($get['limit'])
            ? (intval($get['limit']) === 0 ? 20 : intval($get['limit']))
            : 20;
        $filter->setPagingPage($page-1);
        $filter->setPagingLimit($limit);

        // Full text search
        $fullTextSearch = !empty($get['fulltext_search'])
            ? urldecode($get['fulltext_search'])
            : '';
        $filter->setFulltextSearch($fullTextSearch);

        // Order
        $order = explode('_', isset($get['order']) ? urldecode($get['order']) : 'date_desc');
        $filter->setOrders(array(
            RinkaAsiFilter::ORDER_BY_SITE => $this->config->getConfigValue(array('sourceBase')),
            constant('RinkaAsiFilter::ORDER_BY_'. strtoupper($order[0])) => constant('RinkaAsiFilter::ORDER_'. strtoupper($order[1])),
        ));

        // Filter
        if (!empty($categoryPath)) {
            $filter->setCategory(explode('/', $categoryPath));
        }

        $cityPath = !empty($get['city']) ? urldecode($get['city']) : null;
        if (!empty($cityPath)) {
            $cityPath = explode('/', $cityPath);
            $filter->setCities(array($cityPath));
        } else {
            if (!isset($get['city']) && !isset($get['country'])) {
                $cityPath = $filter->getCity();
            } else {
                $filter->setCities(null);
            }
        }

        try {
            $announcements = $this->bridge->getAnnouncements($filter);
        } catch (RinkaAsiException $e) {
            if ($e->getCode() != RinkaAsiException::E_CONNECTION) {
                throw $e;
            }
            header('Location: '. $this->config->getUrl('base'));
            exit;
        }

        $paginator = new DiggStylePaginator();
        $paginator->items($announcements['totalAds']);
        $paginator->limit($limit);

        $url = $this->config->getUrl('announcementList', array(
            'limit'    => $limit,
            'category' => $categoryPath === null ? '' : $categoryPath,
            'city'     => $cityPath === null ? '' : implode('/', $cityPath),
            'order'    => implode('_', $order)
        ));
        $paginator->target($url);

        $paginator->parameterName('page');
        $paginator->currentPage($page);

        try {
            $categoryTree = $this->bridge->getCategoryTree($this->config->getUrl('announcementList', $get), $categoryPath, false);
            $locations = $this->bridge->getLocations();
        } catch (RinkaAsiException $e) {
            if ($e->getCode() != RinkaAsiException::E_CONNECTION) {
                throw $e;
            }
            header('Location: '. $this->config->getUrl('base'));
            exit;
        }

        return $this->factory->getTemplate('announcements')->render(array(
            'city'          => $cityPath,
            'announcements' => $announcements['ads'],
            'paginator'     => $paginator, // pass the paginator object, later call $pagination->show()
            'categoryTree'  => $categoryTree,
            'formValues'    => array(
                'limit'    => $limit,
                'category' => $categoryPath,
                'fulltext_search' => $fullTextSearch,
                'page'     => $page,
                'order'    => implode('_', $order),
            ),
            'location'      => $locations,
            'orders'        => array(
                'date_desc'  => 'Naujausi viršuje',
                'date_asc'   => 'Seniausi viršuje',
                'price_asc'  => 'Pigiausi viršuje',
                'price_desc' => 'Brangiausi viršuje',
            ),
            'bridge'        => $this->bridge,
            'limits'        => array(5, 10, 20, 50, 100),
            'getData'       => $get,
        ));
    }

    protected function getAnnouncementsFromFilter(RinkaAsiFilter $filter, $setCategory = null) {
        if ($setCategory !== null) {
            $filter->setCategory($setCategory);
        }
        $cacheKey = serialize($filter);
        if (!$announcements = $this->cache->load($cacheKey)) {
            try {
                $this->cache->delayExpire($cacheKey, 60);
                try {
                    $announcements = $this->bridge->getAnnouncements($filter);
                } catch (RinkaAsiException $e) {
                    if ($e->getCode() != RinkaAsiException::E_CONNECTION) {
                        throw $e;
                    }
                    header('Location: '. $this->config->getUrl('base'));
                    exit;
                }
                $this->cache->save($cacheKey, $announcements);
            } catch (Exception $e) {
                $announcements = $this->cache->load($cacheKey, true);
                if (!$announcements) {
                    throw $e;
                }
            }
        }
        return $announcements;
    }

    /**
     * Returns formatted HTML code for announcement box.
     * Provided filter is merged with default one - to show 5 newest announcements in any category.
     *
     * @param RinkaAsiFilter $filter
     * @return string
     */
    public function getAnnouncementsBox(RinkaAsiFilter $filter = null) {
        $filter = ($filter === null) ? new RinkaAsiFilter() : $filter;

        // "Merge" filters
        $pagingLimit = $filter->getPagingLimit();
        $pagingPage  = $filter->getPagingPage();
        $orders      = $filter->getOrders();
        if (empty($pagingLimit)) {
            $filter->setPagingLimit(5);
        }
        if (empty($pagingPage) && $pagingPage !== 0) {
            $filter->setPagingPage(0);
        }
        if (empty($orders)) {
            $filter->setOrders(array(
                RinkaAsiFilter::ORDER_BY_DATE => RinkaAsiFilter::ORDER_DESC,
            ));
        }
        $filter->setSkipCount(true);

        return $this->factory->getTemplate('announcementBox')->render(array(
            'announcements' => array(
            	'tab1' => $this->getAnnouncementsFromFilter($filter, 'parduoda'),
            	'tab2' => $this->getAnnouncementsFromFilter($filter, 'perka'),
            ),
        ));
    }

}