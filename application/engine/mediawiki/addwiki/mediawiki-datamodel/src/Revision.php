<?php

namespace Mediawiki\DataModel;

/**
 * Representation of a version of content
 * @author Addshore
 */
class Revision {

	/**
	 * @var int Id of the revision
	 */
	private $id;

	/**
	 * @var PageIdentifier of the page for the revision
	 */
	private $pageIdentifier;

	/**
	 * @var Content
	 */
	private $content;

	/**
	 * @var EditInfo
	 */
	private $editInfo;

	/**
	 * @var null|string
	 */
	private $user;

	/**
	 * @var null|string
	 */
	private $timestamp;

	/**
	 * @param Content $content
	 * @param PageIdentifier|null $pageIdentifier
	 * @param int|null $revId
	 * @param EditInfo|null $editInfo
	 * @param string|null $user
	 * @param string|null $timestamp
	 */
	public function __construct( Content $content, PageIdentifier $pageIdentifier = null, $revId = null, EditInfo $editInfo = null, $user = null, $timestamp = null ) {
		if( is_null( $editInfo ) ) {
			$editInfo = new EditInfo();
		}
		if( is_null( $pageIdentifier ) ) {
			$pageIdentifier = new PageIdentifier();
		}
		$this->content = $content;
		$this->pageIdentifier = $pageIdentifier;
		$this->id = $revId;
		$this->editInfo = $editInfo;
		$this->user = $user;
		$this->timestamp = $timestamp;
	}

	/**
	 * @return Content
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * @return EditInfo
	 */
	public function getEditInfo() {
		return $this->editInfo;
	}

	/**
	 * @return int|null
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return PageIdentifier|null
	 */
	public function getPageIdentifier() {
		return $this->pageIdentifier;
	}

	/**
	 * @return null|string
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * @return null|string
	 */
	public function getTimestamp() {
		return $this->timestamp;
	}

}