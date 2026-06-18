<?php
namespace mod_qaa\output;

use renderable;
use templatable;
use renderer_base;

defined('MOODLE_INTERNAL') || die();

class comment implements renderable, templatable {
    /** @var int */
    public $id;
    /** @var string */
    public $authorname;
    /** @var string */
    public $authorurl;
    /** @var string HTML */
    public $avatarhtml;
    /** @var string */
    public $datestr;
    /** @var string */
    public $badge;
    /** @var string */
    public $badge_class;
    /** @var string HTML */
    public $contenthtml;
    /** @var bool */
    public $ispending;
    /** @var bool */
    public $canreply;
    /** @var string|null */
    public $replyurl;
    /** @var string|null */
    public $approveurl;
    /** @var string|null */
    public $editurl;
    /** @var string|null */
    public $deleteurl;
    /** @var comment[] */
    public $children;

    /**
     * @param int         $id
     * @param string      $authorname
     * @param string      $authorurl
     * @param string      $avatarhtml
     * @param string      $datestr
     * @param string      $badge
     * @param string      $badge_class
     * @param string      $contenthtml
     * @param bool        $ispending
     * @param bool        $canreply
     * @param string|null $replyurl
     * @param string|null $approveurl
     * @param string|null $editurl
     * @param string|null $deleteurl
     * @param array       $children
     */
    public function __construct(
        $id,
        $authorname,
        $authorurl,
        $avatarhtml,
        $datestr,
        $badge,
        $badge_class,
        $contenthtml,
        $ispending,
        $canreply,
        $replyurl,
        $editurl,
        $deleteurl,
        array $children = []
    ) {
        $this->id = (int)$id;
        $this->authorname = (string)$authorname;
        $this->authorurl = (string)$authorurl;
        $this->avatarhtml = (string)$avatarhtml;
        $this->datestr = (string)$datestr;
        $this->badge = (string)$badge;
        $this->badge_class = (string)$badge_class;
        $this->contenthtml = (string)$contenthtml;
        $this->ispending = (bool)$ispending;
        $this->canreply = (bool)$canreply;
        $this->replyurl = $replyurl;
        $this->editurl = $editurl;
        $this->deleteurl = $deleteurl;
        $this->children = $children;
    }

    public function export_for_template(renderer_base $output): array {
        $children = [];
        foreach ($this->children as $c) {
            $children[] = $c->export_for_template($output);
        }
        return [
            'id'           => $this->id,
            'authorname'   => $this->authorname,
            'authorurl'    => $this->authorurl,
            'avatarhtml'   => $this->avatarhtml,
            'datestr'      => $this->datestr,
            'badge'        => $this->badge,
            'badge_class'  => $this->badge_class,
            'contenthtml'  => $this->contenthtml,
            'ispending'    => $this->ispending,
            'canreply'     => $this->canreply,
            'replyurl'     => $this->replyurl,
            'editurl'      => $this->editurl,
            'deleteurl'    => $this->deleteurl,
            'children'     => $children,
        ];
    }
}
