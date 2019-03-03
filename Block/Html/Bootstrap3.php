<?php

namespace MageArab\BootstrapMenu\Block\Html;

use Magento\Theme\Block\Html\Topmenu;

class BootStrap3 extends Topmenu
{
    /**
     * Get top menu html
     *
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @param int $limit
     * @return string
     */
    protected function _toHtml()
    {
        $this->setModuleName($this->extractModuleName(Topmenu::class));
        return parent::_toHtml();
    }

    /**
     * Add sub menu HTML code for current menu item
     *
     * @param \Magento\Framework\Data\Tree\Node $child
     * @param string $childLevel
     * @param string $childrenWrapClass
     * @param int $limit
     * @return string HTML code
     */
    protected function _addSubMenu($child, $childLevel, $childrenWrapClass, $limit)
    {
        $html = '';
        if (!$child->hasChildren()) {
            return $html;
        }
        $colStops = [];
        if ($childLevel == 0 && $limit) {
            $colStops = $this->_columnBrake($child->getChildren(), $limit);
        }
        $submenuClass = $this->getSubmenuClass() ?: 'submenu';
        $html .= '<ul class="level' . $childLevel . ' ' . $submenuClass . '">';
        $html .= $this->_getHtml($child, $childrenWrapClass, $limit, $colStops);
        $html .= '</ul>';
        return $html;
    }

    /**
     * Recursively generates top menu html from data that is specified in $menuTree
     *
     * @param \Magento\Framework\Data\Tree\Node $menuTree
     * @param string $childrenWrapClass
     * @param int $limit
     * @param array $colBrakes
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getHtml(
        \Magento\Framework\Data\Tree\Node $menuTree,
        $childrenWrapClass,
        $limit,
        array $colBrakes = []
    ) {
        $html = '';

        $children = $menuTree->getChildren();
        $parentLevel = $menuTree->getLevel();
        $childLevel = $parentLevel === null ? 0 : $parentLevel + 1;

        $counter = 1;
        $itemPosition = 1;
        $childrenCount = $children->count();

        $parentPositionClass = $menuTree->getPositionClass();
        $itemPositionClassPrefix = $parentPositionClass ? $parentPositionClass . '-' : 'nav-';

        /** @var \Magento\Framework\Data\Tree\Node $child */
        foreach ($children as $child) {
            if ($childLevel === 0 && $child->getData('is_parent_active') === false) {
                continue;
            }
            $child->setLevel($childLevel);
            $child->setIsFirst($counter == 1);
            $child->setIsLast($counter == $childrenCount);
            $child->setPositionClass($itemPositionClassPrefix . $counter);

            $caretCode = '';
            $anchorAttributes = '';

            if ($child->hasChildren() && $childLevel < 1) {
                $anchorAttributes = 'class="dropdown-toggle" data-toggle="dropdown" role="button" ' .
                    'aria-haspopup="true" aria-expanded="false"';
                $caretCode = ' <span class="caret"></span>';
            }

            if (is_array($colBrakes) && count($colBrakes) && $colBrakes[$counter]['colbrake']) {
                $html .= '</ul></li><li class="column"><ul>';
            }

            $html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . '>';
            $html .= '<a href="' . $child->getUrl() . '" ' . $anchorAttributes . '>' .
                $this->escapeHtml($child->getName()) . $caretCode . '</a>' .
                $this->_addSubMenu(
                    $child,
                    $childLevel,
                    $childrenWrapClass,
                    $limit
                ) . '</li>';
            $itemPosition++;
            $counter++;
        }

        if (is_array($colBrakes) && count($colBrakes) && $limit) {
            $html = '<li class="column"><ul>' . $html . '</ul></li>';
        }
        return $html;
    }

    protected function _getMenuItemClasses(\Magento\Framework\Data\Tree\Node $item)
    {
        $classes = [];
        $levelClassPrefix = $this->getItemLevelClassPrefix() ?: 'level';
        $firstClass = $this->getFirstItemClass() ?: 'first';
        $activeClass = $this->getActiveItemClass() ?: 'active';
        $hasActiveClass = $this->getHasActiveItemClass() ?: 'active';
        $lastClass = $this->getLastItemClass() ?: 'last';
        $parentClass = $this->getParentItemClass() ?:'parent';
        $classes[] = $levelClassPrefix . $item->getLevel();
        $classes[] = $item->getPositionClass();

        if ($item->getIsCategory()) {
            $classes[] = 'category-item';
        }

        if ($item->getIsFirst()) {
            $classes[] = $firstClass;
        }
        if ($item->getIsActive()) {
            $classes[] = $activeClass;
        } elseif ($item->getHasActive()) {
            $classes[] = $hasActiveClass;
        }
        if ($item->getIsLast()) {
            $classes[] = $lastClass;
        }
        if ($item->getClass()) {
            $classes[] = $item->getClass();
        }
        if ($item->hasChildren()) {
            $classes[] = $parentClass;
        }
        return $classes;
    }
}