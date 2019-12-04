<?php

declare(strict_types=1);

namespace Marussia\Pagination;

class Pagination
{
    // Ссылок навигации на страницу
    private $maxItems;

    // Текущая страница
    private $currentPage;

    // Общее количество записей
    private $total;

    // Записей на страницe
    private $limit;
    
    private $first = 1;
    
    private $left = [];
    
    private $right = [];
    
    private $amount;
    
    // Данные по-умолчанию приходят из провайдера
    public function __construct(string $queryString, int $limit = 10, int $maxItems = 10)
    {
        $this->queryString = $queryString;
        
        // Кличество записей на страницe
        $this->limit = $limit;
        
        // Максимальное кол-во ссылок пагинации
        $this->maxItems = $maxItems;
    }
    
    // Переопределяет лимит записей на странице
    public function setLimit(int $limit) : self
    {
        $this->limit = $limit;
        return $this;
    }
    
    // Переопределяет максимальное кол-во ссылок пагинации
    public function setMaxItems(int $maxItems) : self
    {
        $this->maxItems = $maxItems;
        return $this;
    }
    
    public function paginate(int $total, int $currentPage)
    {
        // Устанавливаем общее количество записей
        $this->total = $total;

        // Устанавливаем общee число страниц
        $this->amount = (int) ceil($total / $this->limit);
        
        // Устанавливаем номер текущей страницы
        $this->currentPage = $currentPage;
    
        // Получаем ограничения для цикла
        $limits = $this->limits();
        
        // Генерируем ссылки
        for ($page = $limits['start']; $page <= $limits['end']; $page++) {
            // Если равно это текущая страница
            if ($page === $this->currentPage) {
                continue;
            } elseif ($page < $this->currentPage) {
                $this->left[$page] = $this->getPageLink($page);
            } else {
                $this->right[$page] = $this->getPageLink($page);
            }
        }
    }

    public function current() : Link
    {
        return $this->getPageLink($this->currentPage);
    }
    
    public function left() : array
    {
        return $this->left;
    }
    
    public function right() : array
    {
        return $this->right;
    }

    public function first() : Link
    {
        return $this->getPageLink($this->first);
    }

    public function last() : Link
    {
        return $this->getPageLink($this->amount);
    }
    
    public function prev() : ?Link
    {
        if ($this->currentPage === 1) {
            return null;
        }
        
        return $this->getPageLink($this->currentPage - 1);
    }

    public function next() : ?Link
    {
        if ($this->currentPage === $this->amount) {
            return null;
        }

        return $this->getPageLink($this->currentPage + 1);
    }

    public function isPaginated() : bool
    {
        return $this->total > $this->limit;
    }

    
    private function getPageLink(int $page = 1) : Link
    {
        $link = new Link;
        $link->value = $page;
        
        if (preg_match('(page=[0-9]+)', $this->queryString)) {
            $link->uri = '?' . preg_replace('(page=[0-9]+)', 'page=' . $page, $this->queryString);
        } else {
            $link->uri = '?page=' . $page . '&' . $this->queryString;
        }

        return $link;
    }
    
    private function limits() : array
    {
        // Вычисляем ссылки слева (чтобы активная ссылка была посередине)
        $left = $this->currentPage - round($this->maxItems / 2);
        
        // Вычисляем начало отсчёта
        $start = $left > 0 ? $left : 1;

        // Если впереди есть как минимум $this->maxItems страниц
        if ($start + $this->maxItems <= $this->amount) {
            // Назначаем конец цикла вперёд на $this->maxItems страниц
            $end = $start > 1 ? $start + $this->maxItems : $this->maxItems;
        } else {
            // Конец - общее количество страниц
            $end = $this->amount;

            // Начало - минус $this->maxItems от конца
            $start = $this->amount - $this->maxItems > 0 ? $this->amount - $this->maxItems : 1;
        }

        return ['start' => (int) $start, 'end' => (int) $end];
    }
}
