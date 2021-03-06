<?php

namespace Photo\Service;

use Application\Service\AbstractService;

/**
 * Metadata service. This service implements all functionality related to
 * gathering metadata about photos.
 */
class Metadata extends AbstractService
{

    /**
     * Populates the metadata of a photo based on the EXIF data of the photo
     *
     * @param \Photo\Model\Photo $photo the photo to add the metadata to.
     * @param $path The path where the actual image file is stored
     *
     * @return \Photo\Model\Photo the photo with the added metadata
     */
    public function populateMetadata($photo, $path)
    {
        $exif = read_exif_data($path, 'EXIF');

        if($exif) {
            $photo->setArtist($exif['Artist']);
            $photo->setCamera($exif['Model']);
            $photo->setDateTime(new \DateTime($exif['DateTimeOriginal']));
            $photo->setFlash($exif['Flash'] != 0);
            $photo->setFocalLength($this->frac2dec($exif['FocalLength']));
            $photo->setExposureTime($this->frac2dec($exif['ExposureTime']));
            if(isset($exif['ShutterSpeedValue'])) {
                $photo->setShutterSpeed($this->exifGetShutter($exif['ShutterSpeedValue']));
            }
            if(isset($exif['ShutterSpeedValue'])) {
                $photo->setAperture($this->exifGetFstop($exif['ApertureValue']));
            }
            $photo->setIso($exif['ISOSpeedRatings']);
        } else {
            // We must have a date/time for a photo
            // Since no date is known, we use the current one
            $photo->setDateTime(new \DateTime());
        }
        return $photo;
    }

    /*
     * NOTE: Most code in the following part is copied from 
     * the old site, mostly because I lack knowledge in photography.
     */

    /**
     * Convert a string representing a rational number to a string representing
     * the corresponding decimal approximation.
     *
     * @param string $str the rational number, represented as num+'/'+den
     *
     * @return float the decimal number, represented as float
     */
    private function frac2dec($str)
    {
        if (strpos($str, '/') === false) {
            return $str;
        }
        list($n, $d) = explode('/', $str);

        return $n / $d;//I assume stuff like '234/0' is not supported by EXIF.
    }

    /**
     * Computes the shutter speed from the exif data.
     *
     * @param string $shutterSpeed the shutter speed as listed in the photo's exif data.
     *
     * @return string|null
     */
    private function exifGetShutter($shutterSpeed)
    {
        $apex = $this->frac2dec($shutterSpeed);
        $shutter = pow(2, -$apex);
        if ($shutter == 0) {
            return null;
        }
        if ($shutter >= 1) {
            return round($shutter) . 's';
        }

        return '1/' . round(1 / $shutter) . 's';
    }

    /**
     * Computes the relative aperture from the exif data.
     *
     * @param string $apertureValue the aperture value as listed in the photo's exif data.
     *
     * @return string|null
     */
    private function exifGetFstop($apertureValue)
    {
        $apex = $this->frac2dec($apertureValue);
        $fstop = pow(2, $apex / 2);
        if ($fstop == 0) {
            return null;
        }

        return 'f/' . sprintf("%01.1f", $fstop);
    }

}
