<?php
namespace Globalis\Robo\Core;

class SemanticVersion
{
    const REGEX = "/^v?(?<major>0|[1-9]\d*)\.(?<minor>0|[1-9]\d*)\.(?<patch>0|[1-9]\d*)(?:-(?<special>[\da-z\-]+(?:\.[\da-z\-]+)*))?(?:\+(?<metadata>[\da-z\-]+(?:\.[\da-z\-]+)*))?$/";

    /**
     * @var string
     */
    protected $format = '%M.%m.%p%s';

    /**
     * @var string
     */
    protected $specialSeparator = '-';

    /**
     * @var string
     */
    protected $metadataSeparator = '+';

    /**
     * @var string
     */
    protected $path;

    /**
     * @var array
     */
    protected $version = [
        'major' => 0,
        'minor' => 0,
        'patch' => 0,
        'special' => '',
        'metadata' => '',
    ];

    /**
     * @param string $filename
     */
    public function __construct($currentVersion)
    {
        $this->parse($currentVersion);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $search = ['%M', '%m', '%p', '%s'];
        $replace = $this->version + ['extra' => ''];

        foreach (['special', 'metadata'] as $key) {
            if (!empty($replace[$key])) {
                $separator = $key . 'Separator';
                $replace['extra'] .= $this->{$separator} . $replace[$key];
            }
            unset($replace[$key]);
        }

        return str_replace($search, $replace, $this->format);
    }

    /**
     * @param string $format
     *
     * @return $this
     */
    public function setFormat($format)
    {
        $this->format = $format;
        return $this;
    }

    /**
     * @param string $separator
     *
     * @return $this
     */
    public function setPrereleaseSeparator($separator)
    {
        $this->specialSeparator = $separator;
        return $this;
    }

    /**
     * @param string $what
     *
     * @return SemanticVersion
     *
     * @throws \Exception
     */
    public function increment($what = 'patch')
    {
        switch ($what) {
            case 'major':
                $this->version['major']++;
                $this->version['minor'] = 0;
                $this->version['patch'] = 0;
                break;
            case 'minor':
                $this->version['minor']++;
                $this->version['patch'] = 0;
                break;
            case 'patch':
                $this->version['patch']++;
                break;
            default:
                throw new \Exception('Bad argument, only one of the following is allowed: major, minor, patch');
        }
        return $this;
    }

    /**
     * @param string $tag
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function prerelease($tag = 'RC')
    {
        if (!is_string($tag)) {
            throw new \Exception($this, 'Bad argument, only strings allowed.');
        }

        $number = 0;
        if (!empty($this->version['special'])) {
            list($current, $number) = explode('.', $this->version['special']);
            if ($tag != $current) {
                $number = 0;
            }
        }

        $number++;

        $this->version['special'] = implode('.', [$tag, $number]);
        return $this;
    }

    /**
     * @param array|string $data
     *
     * @return $this
     */
    public function metadata($data)
    {
        if (is_array($data)) {
            $data = implode('.', $data);
        }

        $this->version['metadata'] = $data;
        return $this;
    }

    /**
     * @throws \Exception
     */
    protected function parse($version)
    {
        if (!preg_match_all(self::REGEX, $version, $matches)) {
            throw new \Exception('Bad semver.');
        }

        list(, $major, $minor, $patch, $special, $metadata) = array_map('current', $matches);
        $this->version = compact('major', 'minor', 'patch', 'special', 'metadata');
    }
}
