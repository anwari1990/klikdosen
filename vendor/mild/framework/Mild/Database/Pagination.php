<?php
/**
 * Mild Framework component
 *
 * @author Mochammad Riyadh Ilham Akbar Pasya
 * @link https://github.com/mildphp/mild
 * @copyright 2018
 * @license https://github.com/mildphp/mild/blob/master/LICENSE (MIT Licence)
 */
namespace Mild\Database;

use Countable;
use JsonSerializable;
use IteratorAggregate;
use Mild\Supports\Collection;

class Pagination implements Countable, JsonSerializable, IteratorAggregate
{
    /**
     * @var string
     */
    protected $path = '/';
    /**
     * @var int
     */
    protected $total = 0;
    /**
     * @var Collection
     */
    protected $items;
    /**
     * @var int
     */
    protected $perPage = 1;
    /**
     * @var int
     */
    protected $lastPage;
    /**
     * @var string
     */
    protected $pageName = 'page';
    /**
     * @var array
     */
    protected $query = [];
    /**
     * @var int
     */
    protected $currentPage = 1;
    /**
     * @var callable
     */
    protected static $currentPageResolver;
    /**
     * @var callable
     */
    protected static $currentPathResolver;

    /**
     * Paginator constructor.
     * @param $items
     * @param array $options
     */
    public function __construct($items, array $options)
    {
        foreach ($options as $key => $value) {
            $this->{$key} = $value;
        }
        if (!is_null($this->path) && ($index = strpos($this->path, '?'))) {
            parse_str(substr($this->path, $index + 1), $this->query);
            $this->path = substr($this->path, 0, $index);
        }
        $this->lastPage = ceil($this->total / $this->perPage);
        $this->items = $items instanceof Collection ? $items : new Collection($items);
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return int
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * @return string
     */
    public function getPageName()
    {
        return $this->pageName;
    }

    /**
     * @return array
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * @return int
     */
    public function getLastPage()
    {
        return $this->lastPage;
    }


    /**
     * @return Collection
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->getTotal();
    }

    /**
     * @return string
     */
    public function currentPageUrl()
    {
        return $this->getPageUrl($this->currentPage);
    }

    /**
     * @return string
     */
    public function nextPageUrl()
    {
        return $this->getPageUrl($this->currentPage + 1);
    }

    /**
     * @return string
     */
    public function previousPageUrl()
    {
        return $this->getPageUrl($this->currentPage - 1);
    }

    /**
     * @param int $page
     * @return string
     */
    public function getPageUrl($page)
    {
        if ($page <= 0) {
            $page = 1;
        }
        $this->query[$this->pageName] = $page;
        return $this->path.'?'.http_build_query($this->query, null, '&', PHP_QUERY_RFC3986);
    }

    /**
     * @param int $onEachSide
     * @return string
     */
    public function getLinks($onEachSide = 3)
    {
        if ($this->isDisablePage()) {
            return '';
        }
        $html = '<ul class="pagination" role="navigation">';
        $html .= $this->isDisablePreviousPage() ? '<li class="page-item disabled" aria-disabled="true" aria-label="&laquo; Previous"><span class="page-link" aria-hidden="true">&lsaquo;</span></li>' : '<li class="page-item"><a class="page-link" href="'.$this->previousPageUrl().'" rel="prev" aria-label="&laquo; Previous">&lsaquo;</a></li>';
        foreach ($this->getElements($onEachSide) as $element) {
            if (!is_array($element)) {
                $html .= '<li class="page-item disabled" aria-disabled="true"><span class="page-link">'.$element.'</span></li>';
            } else {
                foreach ($element as $page => $url) {
                    $html .= $this->isActivePage($page) ? '<li class="page-item active" aria-current="page"><span class="page-link">'.$page.'</span></li>' : '<li class="page-item"><a class="page-link" href="'.$url.'">'.$page.'</a></li>';
                }
            }
        }
        $html .= $this->isDisableNextPage() ? '<li class="page-item disabled" aria-disabled="true" aria-label="Next &raquo;"><span class="page-link" aria-hidden="true">&rsaquo;</span></li>' : '<li class="page-item"><a class="page-link" href="'.$this->nextPageUrl() .'" rel="next" aria-label="Next &raquo;">&rsaquo;</a></li>';
        $html  .= '<ul>';
        return $html;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        if ($this->isDisablePage()) {
            return '';
        }
        $html = '<ul class="pagination" role="navigation">';
        $html .= $this->isDisablePreviousPage() ? '<li class="page-item disabled" aria-disabled="true"><span class="page-link">&laquo; Previous</span></li>' : '<li class="page-item"><a class="page-link" href="'.$this->previousPageUrl().'" rel="prev">&laquo; Previous</a></li>';
        $html .= $this->isDisableNextPage() ? '<li class="page-item disabled" aria-disabled="true"><span class="page-link">Next &raquo;</span></li>' : '<li class="page-item"><a class="page-link" href="'.$this->nextPageUrl().'" rel="next">Next &raquo;</a></li>';
        $html .= '</ul>';
        return $html;
    }

    /**
     * @param int $onEachSide
     * @return array
     */
    public function getElements($onEachSide = 3)
    {
        $onEachSide = $onEachSide > 2 ? $onEachSide : 3;
        $window = (int) $onEachSide * 2;
        $last = [];
        $first = [];
        $elements = [];
        $slider = null;
        foreach (range(1, 2) as $page) {
            $first[$page] = $this->getPageUrl($page);
        }
        foreach (range($this->lastPage - 1, $this->lastPage) as $page) {
            $last[$page] = $this->getPageUrl($page);
        }
        if ($this->lastPage < $window + 6) {
            $elements['first'] = null;
            foreach (range(1, $this->lastPage) as $page) {
                $elements['first'][$page] = $this->getPageUrl($page);
            }
            $elements['last'] = null;
            $elements['slider'] = null;
        } elseif ($this->currentPage <= $window) {
            foreach (range(1, $window + 2) as $page) {
                $elements['first'][$page] = $this->getPageUrl($page);
            }
            $elements['last'] = $last;
            $elements['slider'] = $slider;
        } elseif ($this->currentPage > ($this->lastPage - $window)) {
            $elements['first'] = $first;
            foreach (range($this->lastPage - ($window + 2), $this->lastPage) as $page) {
                $elements['last'][$page] = $this->getPageUrl($page);
            }
            $elements['slider'] = $slider;
        } else {
            $elements['first'] = $first;
            foreach (range($this->currentPage - $onEachSide, $this->currentPage + $onEachSide) as $page) {
                $elements['slider'][$page] = $this->getPageUrl($page);
            }
            $elements['last'] = $last;
        }
        $elements = array_filter([$elements['first'], is_array($elements['slider']) ? '...' : null, $elements['slider'], is_array($elements['last']) ? '...' : null, $elements['last']]);
        return $elements;
    }

    /**
     * @param $page
     * @return bool
     */
    public function isActivePage($page)
    {
        return $this->currentPage == $page;
    }

    /**
     * @return bool
     */
    public function isDisablePage()
    {
        return $this->currentPage > $this->lastPage || $this->total < 0;
    }

    /**
     * @return bool
     */
    public function isDisableNextPage()
    {
        return $this->currentPage >= $this->lastPage;
    }

    /**
     * @return bool
     */
    public function isDisablePreviousPage()
    {
        return $this->currentPage <= 1;
    }

    /**
     * @return \Traversable|void
     */
    public function getIterator()
    {
        return $this->items->getIterator();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'firstPage' => 1,
            'total' => $this->total,
            'perPage' => $this->perPage,
            'lastPage' => $this->lastPage,
            'firstUrl' => $this->getPageUrl(1),
            'data' => $this->items->toArray(),
            'nextUrl' => $this->nextPageUrl(),
            'elements' => $this->getElements(),
            'currentPage' => $this->currentPage,
            'currentUrl' => $this->currentPageUrl(),
            'lastUrl' => $this->getPageUrl($this->lastPage),
            'previousUrl' => $this->previousPageUrl(),
            'isDisablePage' => $this->isDisablePage(),
            'isDisableNextPage' => $this->isDisableNextPage(),
            'isDisablePreviousPage' => $this->isDisablePreviousPage(),
            'nextPage' => $this->isDisableNextPage() ? null : $this->currentPage + 1,
            'previousPage' => $this->isDisablePreviousPage() ? null : $this->currentPage - 1,
        ];
    }

    /**
     * @return mixed
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @param int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Determine if the method on the class does not exist, we call the collection method
     *
     * @param $name
     * @param $arguments
     * @return mixed
     * @see Collection
     */
    public function __call($name, $arguments)
    {
        return $this->items->$name(...$arguments);
    }

    /**
     * @param $resolver
     * @return void
     */
    public static function setCurrentPathResolver($resolver)
    {
        static::$currentPathResolver = $resolver;
    }

    /**
     * @param string $default
     * @return string
     */
    public static function resolveCurrentPath($default = '/')
    {
        if (is_null(static::$currentPathResolver)) {
            return $default;
        }
        return call_user_func(static::$currentPathResolver);
    }

    /**
     * @param string $page
     * @param int $default
     * @return int
     */
    public static function resolveCurrentPage($page = 'page', $default = 1)
    {
        if (is_null(static::$currentPageResolver)) {
            return $default;
        }
        return call_user_func(static::$currentPageResolver, $page);
    }

    /**
     * @param callable $resolver
     * @return void
     */
    public static function setCurrentPageResolver($resolver)
    {
        static::$currentPageResolver = $resolver;
    }
}