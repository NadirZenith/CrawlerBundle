<?php

namespace Nz\CrawlerBundle\Model;

/**
 * @author nz
 */
interface LinkInterface
{

    /**
     * Returns the id.
     *
     * @return mixed
     */
    public function getId();

    /**
     * @param mixed $id
     */
    public function setId($id);

    /**
     * Get url.
     *
     * @return string
     */
    public function getUrl();

    /**
     * Set Url.
     *
     * @param string $url
     */
    public function setUrl($url);

    /**
     * Get processed.
     *
     * @return bool $processed
     */
    public function getProcessed();

    /**
     * Set processed.
     *
     * @param bool $processed
     */
    public function setProcessed($processed);

    /**
     * Get hasError.
     *
     * @return bool $hasError
     */
    public function getHasError();

    /**
     * If occurs any error when processing url
     *
     * Set hasError
     *
     * @param bool $hasError
     */
    public function setHasError($hasError);

    /**
     * Get skip.
     *
     * @return bool $skip
     */
    public function getSkip();

    /**
     * If occurs any new error
     *
     * Set skip
     *
     * @param bool $skip
     */
    public function setSkip($skip);

    /**
     * Set notes.
     *
     * @param string $notes
     */
    public function setNotes(array $notes = array());

    /**
     * Get notes.
     *
     * @return array
     */
    public function getNotes();

    /**
     * Add note.
     *
     * @param string $notes
     */
    public function setNote($name, $value);

    /**
     * Get note.
     *
     * @param string $name note name
     * @return string | null;
     */
    public function getNote($name);

    /**
     * remove note.
     *
     * @param string $name note name
     */
    public function removeNote($name);

    /**
     * Get crawledAt
     * 
     * @return \DateTime $crawledAt
     */
    public function getCrawledAt();

    /**
     * Set crawledAt
     * 
     * @param \DateTime $crawledAt
     */
    public function setCrawledAt(\DateTime $crawledAt = null);
}
