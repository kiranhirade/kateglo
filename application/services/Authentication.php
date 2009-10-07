<?php
namespace kateglo\application\services;
/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the GPL 2.0. For more information, see
 * <http://code.google.com/p/kateglo/>.
 */
use kateglo\application\helpers;
use kateglo\application\services\exceptions;
/**
 * 
 * 
 * @uses Exception
 * @package kateglo\application\configs
 * @license <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html> GPL 2.0
 * @link http://code.google.com/p/kateglo/
 * @since  
 * @version 0.0
 * @author  Arthur Purnama <arthur@purnama.de>
 * @copyright Copyright (c) 2009 Kateglo (http://code.google.com/p/kateglo/)
 */
class Authentication {

	/**
	 * 
	 * @return boolean
	 */
	public function hasIdentity(){
		return \Zend_Auth::getInstance()->hasIdentity();
	}
	
	/**
	 * 
	 * @return kateglo\application\models\User
	 */
	public function getIdentity(){
		return \Zend_Auth::getInstance()->getIdentity();	
	}
	
	/**
	 * 
	 * @return kateglo\application\models\User
	 */
	public function clearIdentity(){
		return \Zend_Auth::getInstance()->clearIdentity();	
	}
	
	/**
	 * 
	 * @param string $username
	 * @param string $password
	 * @throws kateglo\application\services\AuthenticationException
	 */
	public function authenticate($username, $password){
		$auth = \Zend_Auth::getInstance();
		
		$result = $auth->authenticate(new helpers\AuthenticationAdapter($username, $password));
		
		if(!$result->isValid()){
			throw new exceptions\AuthenticationException(implode("", $result->getMessages()));
		}
	}
}

?>