<?php

namespace Activity\Model;

use Doctrine\ORM\Mapping as ORM;
use User\Model\User;

/**
 * Activity model.
 *
 * @ORM\Entity
 */
class Activity
{
    /**
     * ID for the activity.
     *
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * Name for the activity.
     *
     * @Orm\Column(type="string")
     */
    protected $name;

    /**
     * The date and time the activity starts.
     *
     * @ORM\Column(type="datetime")
     */
    protected $beginTime;

    /**
     * The date and time the activity ends.
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $endTime;

    /**
     * The location the activity is held at.
     *
     * @ORM\Column(type="string")
     */
    protected $location;

    /**
     * How much does it cost. 0 = free!
     *
     * @ORM\Column(type="integer")
     */
    protected $costs;

    /**
     * Are people able to sign up for this activity?
     *
     * @ORM\Column(type="boolean")
     */
    protected $canSignUp;

    /**
     * Are people outside of GEWIS allowed to sign up
     * N.b. if $canSignUp is false, this column does not matter.
     *
     * @ORM\Column(type="boolean")
     */
    protected $onlyGEWIS;

    /**
     * Who did approve this activity.
     *
     * @ORM\ManyToOne(targetEntity="User\Model\User")
     * @ORM\JoinColumn(referencedColumnName="lidnr")
     */
    protected $approver;

    /**
     * Who created this activity.
     *
     * @ORM\Column(nullable=false)
     * @ORM\ManyToOne(targetEntity="User\Model\User", inversedBy="roles")
     * @ORM\JoinColumn(referencedColumnName="lidnr")
     */
    protected $creator;

    /**
     * Is this activity approved.
     *
     * @ORM\Column(type="boolean")
     */
    protected $approved;

    /**
     * Activity description.
     *
     * @Orm\Column(type="text")
     */
    protected $description;

    /**
     * all the photo's in this album.
     *
     * @ORM\OneToMany(targetEntity="ActivitySignup", mappedBy="activity")
     */
    protected $signUps;

    // TODO -> where can i find member organ?
    protected $organ;

    public function get($variable)
    {
        return $this->$variable;
    }

    /**
     * Create a new activity.
     *
     * @param array $params Parameters for the new activity
     *
     * @throws \Exception If a activity is loaded
     * @throws \Exception If a necessary parameter is not set
     *
     * @return \Activity\Model\Activity the created activity
     */
    public function create(array $params)
    {
        if ($this->id != null) {
            throw new \Exception('There is already a loaded activity');
        }
        foreach (['description', 'name', 'beginTime', 'endTime', 'costs', 'location', 'creator', 'canSignUp'] as $param) {
            if (!isset($params[$param])) {
                throw new \Exception("create: parameter $param not set");
            }
            $this->$param = $params[$param];
        }

        /** @var $user User*/
        $user = $params['creator'];

        $this->beginTime = new \DateTime($this->beginTime);
        $this->endTime = new \DateTime($this->endTime);
        $this->creator = $user->getLidNr();

        // TODO: These values need to be set correctly
        $this->onlyGEWIS = true;
        $this->approved = 0;

        return $this;
    }

    /**
     * Returns if an user can sign up for this activity.
     */
    public function canSignUp()
    {
        return $this->canSignUp;
    }
}
