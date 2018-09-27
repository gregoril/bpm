<?php
namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use ProcessMaker\Models\Process;
use ProcessMaker\Models\ProcessRequest;
use ProcessMaker\Models\User;
use Tests\Feature\Shared\ResourceAssertionsTrait;
use Tests\TestCase;

/**
 * Test the process execution with requests
 *
 * @group process_tests
 */
class ProcessScriptsTest extends TestCase
{

    use DatabaseTransactions;
    use ResourceAssertionsTrait;
    use WithFaker;

    /**
     *
     * @var User $user 
     */
    protected $user;

    /**
     * @var Process $process
     */
    protected $process;
    private $requestStructure = [
        'uuid',
        'process_uuid',
        'user_uuid',
        'status',
        'name',
        'initiated_at',
        'created_at',
        'updated_at'
    ];

    /**
     * Initialize the controller tests
     *
     */
    protected function setUp()
    {
        parent::setUp();
        //Login as an valid user
        $this->user = factory(User::class)->create();
        $this->actingAs($this->user, 'api');
        $this->process = $this->createTestProcess();
    }

    /**
     * Create a single task process assigned to $this->user
     */
    private function createTestProcess(array $data = [])
    {
        $data['bpmn'] = Process::getProcessTemplate('ScriptTasks.bpmn');
        $process = factory(Process::class)->create($data);
        return $process;
    }

    /**
     * Execute a process
     */
    public function testExecuteAProcess()
    {
        //Start a process request
        $route = route('process_events.trigger', [$this->process->uuid_text, 'event' => '_2']);
        $data = [];
        $response = $this->json('POST', $route, $data);
        //Verify status
        $this->assertStatus(201, $response);
        //Verify the structure
        $response->assertJsonStructure($this->requestStructure);
        $request = $response->json();
        //Get the active tasks of the request
        $route = route('tasks.index');
        $response = $this->json('GET', $route);
        $tasks = $response->json('data');
        //Check that two script tasks were completed.
        $this->assertArraySubset([
            [
                'element_type' => 'scriptTask',
                'status' => 'CLOSED',
            ],
            [
                'element_type' => 'scriptTask',
                'status' => 'CLOSED',
            ]], $tasks);
        //Get process instance
        $processInstance = ProcessRequest::withUuid($tasks[0]['process_request_uuid'])->firstOrFail();
        //Check the data
        $this->assertArrayHasKey('random', $processInstance->data);
        $this->assertArrayHasKey('double', $processInstance->data);
        $this->assertInternalType('int', $processInstance->data['random']);
        $this->assertInternalType('int', $processInstance->data['double']);
        $this->assertEquals(2 * $processInstance->data['random'], $processInstance->data['double']);
    }
}