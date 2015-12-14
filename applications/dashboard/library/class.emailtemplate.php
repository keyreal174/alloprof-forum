<?php

/**
 * Class EmailTemplate
 *
 * Compiles the data for an email, applies appropriate content filters and renders the email.
 *
 */
class EmailTemplate extends Gdn_Pluggable {

    /**
     * Delimiter for plaintext email.
     */
    const PLAINTEXT_START = '<!-- //TEXT VERSION FOLLOWS//';

    /**
     * Default email colors.
     */
    const DEFAULT_TEXT_COLOR = '#333333';
    const DEFAULT_BACKGROUND_COLOR = '#eeeeee';
    const DEFAULT_CONTAINER_BACKGROUND_COLOR = '#ffffff';
    const DEFAULT_BUTTON_BACKGROUND_COLOR = '#38abe3'; // Vanilla blue
    const DEFAULT_BUTTON_TEXT_COLOR = '#ffffff';

    /**
     * @var string The HTML formatted email title.
     */
    protected $title;
    /**
     * @var string The HTML formatted email lead (sub-title, appears under title).
     */
    protected $lead;
    /**
     * @var string The HTML formatted email message (the body of the email).
     */
    protected $message;
    /**
     * @var string The HTML formatted email footer.
     */
    protected $footer;
    /**
     * @var array An array representing a button with the following keys:
     * 'url' => The href value of the button.
     * 'text' => The button text.
     * 'textColor' => The hex color code of the button text, must include the leading '#'.
     * 'backgroundColor' => The hex color code of the button background, must include the leading '#'.
     */
    protected $button;
    /**
     * @var array An array representing an image with the following keys:
     * 'source' => The image source url.
     * 'link' => The href value of the image wrapper.
     * 'alt' => The alt value of the image tag.
     */
    protected $image;
    /**
     * @var string The path to the email view.
     */
    protected $view;
    /**
     * @var bool Whether to render in plaintext.
     */
    protected $plaintext = false;
    // Colors
    /**
     * @var string The hex color code of the text, must include the leading '#'.
     */
    protected $textColor = self::DEFAULT_TEXT_COLOR;
   /**
    * @var string The hex color code of the background, must include the leading '#'.
    */
    protected $backgroundColor = self::DEFAULT_BACKGROUND_COLOR;
    /**
     * @var string The hex color code of the container background, must include the leading '#'.
     */
    protected $containerBackgroundColor = self::DEFAULT_CONTAINER_BACKGROUND_COLOR;
    /**
     * @var string The default hex color code of the button text, must include the leading '#'.
     */
    protected $defaultButtonTextColor = self::DEFAULT_BUTTON_TEXT_COLOR;
    /**
     * @var string The default hex color code of the button background, must include the leading '#'.
     */
    protected $defaultButtonBackgroundColor = self::DEFAULT_BUTTON_BACKGROUND_COLOR;

    /**
     * @param string $message HTML formatted email message (the body of the email).
     * @param string $title HTML formatted email title.
     * @param string $lead HTML formatted email lead (sub-title, appears under title).
     * @param string $view
     * @throws Exception
     */
    function __construct($message = '', $title = '', $lead = '', $view = 'email-basic') {
	$this->setMessage($message);
	$this->setTitle($title);
	$this->setLead($lead);

	$this->view = Gdn::controller()->fetchViewLocation($view, 'email', 'dashboard');
    }

    /**
     * Filters an unsafe HTML string and returns it.
     *
     * @param string $html The HTML to filter.
     * @param bool $convertNewlines Whether to convert new lines to html br tags.
     * @return string The filtered HTML string.
     */
    protected function formatContent($html, $convertNewlines = false) {
	$str = Gdn_Format::htmlFilter($html);
	if ($convertNewlines) {
	    $str = preg_replace('/(\015\012)|(\015)|(\012)/', '<br>', $str);
	}
	// $str = strip_tags($str, ['b', 'i', 'p', 'strong', 'em', 'br']);
	return $str;
    }

    /**
     * @param string $view The view name.
     * @param string $controllerName The controller name for the view.
     * @param string $applicationFolder The application folder for the view.
     * @return EmailTemplate $this The calling object.
     * @throws Exception
     */
    public function setView($view, $controllerName = 'email', $applicationFolder = 'dashboard') {
        $this->view = Gdn::controller()->fetchViewLocation($view, $controllerName, $applicationFolder);
        return $this;
    }

    /**
     * @return string The HTML formatted email title.
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @param string $title The HTML formatted email title.
     * @return EmailTemplate $this The calling object.
     */
    public function setTitle($title) {
        $this->title = $this->formatContent($title);
        return $this;
    }

    /**
     * @return string The HTML formatted email lead (sub-title, appears under title).
     */
    public function getLead() {
        return $this->lead;
    }

    /**
     * @param string $lead The HTML formatted email lead (sub-title, appears under title).
     * @return EmailTemplate $this The calling object.
     */
    public function setLead($lead) {
        $this->lead = $this->formatContent($lead);
        return $this;
    }

    /**
     * @return string The HTML formatted email message (the body of the email).
     */
    public function getMessage() {
        return $this->message;
    }

    /**
     * @param string $message The HTML formatted email message (the body of the email).
     * @param bool $convertNewlines Whether to convert new lines to html br tags.
     * @return EmailTemplate $this The calling object.
     */
    public function setMessage($message, $convertNewlines = false){
	$this->message = $this->formatContent($message, $convertNewlines);
        return $this;
    }

    /**
     * @return string The HTML formatted email footer.
     */
    public function getFooter() {
        return $this->footer;
    }

    /**
     * @param string $footer The HTML formatted email footer.
     * @return EmailTemplate $this The calling object.
     */
    public function setFooter($footer) {
	$this->footer = $this->formatContent($footer);
        return $this;
    }

    /**
     * @return string The hex color code of the text.
     */
    public function getTextColor() {
	return $this->textColor;
    }

    /**
     * @param string $color The hex color code of the text, must include the leading '#'.
     * @return EmailTemplate $this The calling object.
     */
    public function setTextColor($color) {
	$this->textColor = htmlspecialchars($color);
	return $this;
    }

    /**
     * @return string The hex color code of the background.
     */
    public function getBackgroundColor() {
        return $this->backgroundColor;
    }

    /**
     * @param string $color The hex color code of the background, must include the leading '#'.
     * @return EmailTemplate $this The calling object.
     */
    public function setBackgroundColor($color) {
        $this->backgroundColor = htmlspecialchars($color);
        return $this;
    }

    /**
     * @return string The hex color code of the container background.
     */
    public function getContainerBackgroundColor() {
	return $this->containerBackgroundColor;
    }

    /**
     * @param string $color The hex color code of the container background, must include the leading '#'.
     * @return EmailTemplate $this The calling object.
     */
    public function setContainerBackgroundColor($color) {
	$this->containerBackgroundColor = htmlspecialchars($color);
	return $this;
    }

    /**
     * @return string The default hex color code of the button text, must include the leading '#'.
     */
    public function getDefaultButtonTextColor() {
	return $this->defaultButtonTextColor;
    }

    /**
     * Sets the default color for the button text.
     * The text color of the EmailTemplate's button property can be overridden by setting $button['textColor']
     *
     * @param string $color The default hex color code of the button text, must include the leading '#'.
     * @return EmailTemplate $this The calling object.
     */
    public function setDefaultButtonTextColor($color) {
	$this->defaultButtonTextColor = $color;
        return $this;
    }

    /**
     * @return string The default hex color code of the button background, must include the leading '#'.
     */
    public function getDefaultButtonBackgroundColor() {
	return $this->defaultButtonBackgroundColor;
    }

    /**
     * Sets the default color for the button background.
     * The background color of the EmailTemplate's button property can be overridden by setting $button['backgroundColor']
     *
     * @param string $color The default hex color code of the button background, must include the leading '#'.
     * @return EmailTemplate $this The calling object.
     */
    public function setDefaultButtonBackgroundColor($color) {
	$this->defaultButtonBackgroundColor = $color;
	return $this;
    }

    /**
     * @return bool Whether to render in plaintext.
     */
    public function isPlaintext() {
	return $this->plaintext;
    }

    /**
     * @param bool $plainText Whether to render in plaintext.
     */
    public function setPlaintext($plainText) {
	$this->plaintext = $plainText;
    }

    /**
     * Set the button property.
     *
     * @param string $url The href value of the button.
     * @param string $text The button text.
     * @param string $textColor The hex color code of the button text, must include the leading '#'.
     * @param string $backgroundColor The hex color code of the button background, must include the leading '#'.
     * @return EmailTemplate $this The calling object.
     */
    public function setButton($url, $text, $textColor = '', $backgroundColor = '') {
	if (!$textColor) {
	    $textColor = $this->defaultButtonTextColor;
	}
        if (!$backgroundColor) {
            $backgroundColor = $this->defaultButtonBackgroundColor;
        }
        $this->button = array('url' => htmlspecialchars($url),
                              'text' => htmlspecialchars($this->formatContent($text)),
			      'textColor' => htmlspecialchars($textColor),
                              'backgroundColor' => htmlspecialchars($backgroundColor));
        return $this;
    }

    /**
     * @return array An array representing an image with the following keys:
     * 'source' => The image source url.
     * 'link' => The href value of the image wrapper.
     * 'alt' => The alt value of the image tag.
     */
    public function getImage() {
        return $this->image;
    }

    /**
     * Set the image property.
     *
     * @param string $sourceUrl The image source url.
     * @param string $linkUrl  The href value of the image wrapper.
     * @param string $alt The alt value of the img tag.
     * @return EmailTemplate $this The calling object.
     */
    public function setImage($sourceUrl = '', $linkUrl = '', $alt = '') {
        // We need either a source image or an alt to have an img tag.
        if ($sourceUrl || $alt) {
            $this->image = array('source' => htmlspecialchars($sourceUrl),
                'link' => htmlspecialchars($linkUrl),
                'alt' => $alt);
        }
        return $this;
    }

    /**
     * Set the image property using an array with the following keys:
     * 'source' => The image source url.
     * 'link' => The href value of the image wrapper.
     * 'alt' => The alt value of the img tag.
     * @return EmailTemplate $this The calling object.
     */
    public function setImageArray($image) {
        $this->setImage(val('source', $image), val('link', $image), val('alt', $image));
        return $this;
    }

    /**
     * Copies the email object to an array. A simple (array) typecast won't work,
     * since the properties are protected and as such, add unwanted information to the array keys.
     *
     * @param EmailTemplate $email The email object.
     * @return array Copy of email object in an array format for output.
     */
    protected function objectToArray($email) {
        if (is_array($email) || is_object($email)) {
            $result = array();
            foreach ($email as $key => $value) {
                $result[$key] = $this->objectToArray($value);
            }
            return $result;
        }
        return $email;
    }

    /**
     * Renders a plaintext email.
     *
     * @return string A plaintext email.
     */
    protected function plainTextEmail() {
        $email = array(
            'banner' => val('alt', $this->image).' '.val('link', $this->image),
	    'title' => $this->getTitle(),
	    'lead' => $this->getLead(),
	    'message' => $this->getMessage(),
	    'button' => sprintf(t('%s: %s'), val('text', $this->button), val('url', $this->button)),
	    'footer' => $this->getFooter()
	);
	// Don't repeat the title twice.
	if (strpos($this->getMessage, $this->getTitle()) === 0) {
	    unset($email['title']);
	}
	$email = implode('<br><br>', $email);
        $email = Gdn_Format::plainText($email);
        return $email;
    }

    /**
     * Render the email.
     *
     * @return string The rendered email.
     */
    public function toString() {
        if ($this->isPlaintext()) {
            return $this->plainTextEmail();
        }
        $controller = new Gdn_Controller();
        $controller->setData('email', $this->objectToArray($this));
        $email = $controller->fetchView($this->view);
        // Append plaintext version
        $email .= self::PLAINTEXT_START.$this->plainTextEmail();
        return $email;
    }
}
