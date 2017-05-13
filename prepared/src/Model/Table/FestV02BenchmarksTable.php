<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * FestV02Benchmarks Model
 *
 * @method \App\Model\Entity\FestV02Benchmark get($primaryKey, $options = [])
 * @method \App\Model\Entity\FestV02Benchmark newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\FestV02Benchmark[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\FestV02Benchmark|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\FestV02Benchmark patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\FestV02Benchmark[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\FestV02Benchmark findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class FestV02BenchmarksTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('fest_v02_benchmarks');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('instance_code', 'create')
            ->notEmpty('instance_code');

        $validator
            ->requirePresence('hostname', 'create')
            ->notEmpty('hostname');

        $validator
            ->dateTime('instance_begin')
            ->requirePresence('instance_begin', 'create')
            ->notEmpty('instance_begin');

        $validator
            ->dateTime('event_time')
            ->requirePresence('event_time', 'create')
            ->notEmpty('event_time');

        $validator
            ->requirePresence('event_class', 'create')
            ->notEmpty('event_class');

        $validator
            ->requirePresence('event_function', 'create')
            ->notEmpty('event_function');

        $validator
            ->requirePresence('event', 'create')
            ->notEmpty('event');

        $validator
            ->requirePresence('detail', 'create')
            ->notEmpty('detail');

        return $validator;
    }

    /**
     * Returns the database connection name to use by default.
     *
     * @return string
     */
    public static function defaultConnectionName()
    {
        return 'fest';
    }
}
