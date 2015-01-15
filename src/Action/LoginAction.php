<?php

namespace CrudUsers\Action;

use Crud\Action\BaseAction;
use Crud\Event\Subject;
use Crud\Traits\RedirectTrait;

class LoginAction extends BaseAction
{

    use RedirectTrait;

    protected $_defaultConfig = [
        'enabled' => true,
        // FIXME Should flash messages use the AuthComponent::flash() or it's key?
        'messages' => [
            'success' => [
                'text' => 'Successfully logged you in'
            ],
            'error' => [
                'text' => 'Invalid credentials, please try again'
            ]
        ],
    ];

    /**
     * HTTP GET handler
     *
     * @return void|\Cake\Network\Response
     */
    protected function _get()
    {
        $subject = $this->_subject([
            'success' => true,
        ]);

        $this->_trigger('beforeRender', $subject);
    }

    /**
     * HTTP POST handler
     *
     * @return void|\Cake\Network\Response
     */
    protected function _post()
    {
        $subject = $this->_subject([
            // FIXME Are those really needed?
            'identifyMethod' => 'identify',
            'redirectUrlMethod' => 'redirectUrl',
        ]);

        $this->_trigger('beforeLogin', $subject);

        $authCallback = [$this->_controller()->Auth, $subject->identifyMethod];
        if ($user = $authCallback()){
            return $this->_success($subject, $user);
        }

        return $this->_error($subject);
    }

    /**
     * Post success callback
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @param array Authenticated user record data.
     * @return \Cake\Network\Response
     */
    protected function _success(Subject $subject, array $user)
    {
        $subject->set(['success' => true, 'user' => $user]);

        $this->_trigger('afterLogin', $subject);
        $this->_controller()->Auth->setUser($subject->user);
        $this->setFlash('success', $subject);

        return $this->_redirect(
            $subject,
            $this->_controller()->Auth->{$subject->redirectUrlMethod}()
        );
    }

    /**
     * Post error callback
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @return void
     */
    protected function _error(Subject $subject)
    {
        $subject->set(['success' => false]);

        $this->_trigger('afterLogin', $subject);
        $this->setFlash('error', $subject);
        $this->_trigger('beforeRender', $subject);
    }
}
