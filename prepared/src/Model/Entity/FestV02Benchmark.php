<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * FestV02Benchmark Entity
 *
 * @property int $id
 * @property string $instance_code
 * @property string $hostname
 * @property \Cake\I18n\Time $instance_begin
 * @property \Cake\I18n\Time $event_time
 * @property string $event_class
 * @property string $event_function
 * @property string $event
 * @property string $detail
 * @property \Cake\I18n\Time $created
 */
class FestV02Benchmark extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
}
