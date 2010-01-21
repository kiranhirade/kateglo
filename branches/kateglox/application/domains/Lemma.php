<?php
namespace kateglo\application\domains;
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

use kateglo\application\domains\exceptions;
use kateglo\application\utilities;
use kateglo\application\models;
use Doctrine\ORM\Mapping;
/**
 *
 *
 * @package kateglo\application\domains
 * @license <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html> GPL 2.0
 * @link http://code.google.com/p/kateglo/
 * @since
 * @version 0.0
 * @author  Arthur Purnama <arthur@purnama.de>
 * @copyright Copyright (c) 2009 Kateglo (http://code.google.com/p/kateglo/)
 */
class Lemma {

	/**
	 *
	 * @param string $lemma
	 * @return kateglo\application\models\Lemma
	 */
	public static function getByLemma($lemma){
		$query = utilities\DataAccess::getEntityManager()->createQuery("SELECT l FROM ".models\Lemma::CLASS_NAME." l WHERE l.lemma = '$lemma'");
		$result = $query->getResult();
		if(count($result) === 1){
			if(! ($result[0] instanceof models\Lemma)){
				throw new exceptions\DomainObjectNotFoundException("wrong result");
			}
		}else{
			throw new exceptions\DomainResultEmptyException("result not found");
		}

		return $result[0];
	}

	/**
	 *
	 * @return int
	 */
	public static function getTotalCount(){
		$query = utilities\DataAccess::getEntityManager()->createQuery("SELECT COUNT(l.id) FROM ".models\Lemma::CLASS_NAME." l ");
		$result = $query->getSingleResult();

		if(! ( is_numeric($result[1]) )){var_dump($result); die();
		throw new exceptions\DomainResultEmptyException("result not found");
		}
			

		return $result[1];
	}

	/**
	 *
	 * @param int $limit
	 * @return kateglo\application\utilities\collections\ArrayCollection
	 */
	public static function getRandom($limit = 10){

		$randomIdResult = utilities\DataAccess::getConnection()->query("SELECT lemma_id FROM lemma ORDER BY RAND() LIMIT ".$limit." ; ");
		$idArray = array();
		foreach($randomIdResult as $idResult){
			$idArray[] = $idResult['lemma_id'];
		}

		$sql = "SELECT l FROM ".models\Lemma::CLASS_NAME." l WHERE l.id IN ('".implode("','", $idArray)."');";

		$query = utilities\DataAccess::getEntityManager()->createQuery($sql);
		$result = $query->getResult();
		if(count($result) > 0){
			return $result;
		}else{
			throw new exceptions\DomainResultEmptyException("result not found");
		}
	}

	/**
	 * 
	 * @param int $offset
	 * @param int $limit
	 * @param string $lemma
	 * @param array $type
	 * @param string $definition
	 * @param array $lexical
	 * @param string $orderBy
	 * @param string $direction
	 * @return kateglo\application\utilities\collections\ArrayCollection
	 */
	public static function getLists($offset = 0, $limit = 50, $lemma = "", array $type, $definition = "", array $lexical, $orderBy = "", $direction = "ASC"){
		$sqlSelect = "SELECT l";
		$sqlFrom = "FROM ".models\Lemma::CLASS_NAME." l";
		
		$whereClause = "";

		if($lemma !== ""){
			$whereClause .= " l.lemma LIKE '".$lemma."%' ";
		}

		if($definition !== ""){
			$sqlSelect .= ", d";
			$sqlFrom .= " LEFT JOIN l.definitions d";
			if($whereClause !== ""){
				$whereClause .= " AND ";
			}
			$whereClause .= " d.definition LIKE '".$definition."%' ";
		}

		if(count($type) > 0){
			$sqlSelect .= ", t";
			$sqlFrom .= " LEFT JOIN l.types t";
			if($whereClause !== ""){
				$whereClause .= " AND ";
			}
			$whereClause .= " t.type IN ('".implode("','", $type)."') ";
		}

		if(count($lexical) > 0){
			if($definition !== ""){
				$sqlSelect .= ", lx";
				$sqlFrom .= " LEFT JOIN d.lexical lx";
			}else{
				$sqlSelect .= ", d, lx";
				$sqlFrom .= " LEFT JOIN l.definitions d LEFT JOIN d.lexical lx";
			}
			if($whereClause !== ""){
				$whereClause .= " AND ";
			}
			$whereClause .= " lx.lexical IN ('".implode("','", $lexical)."') ";
		}
		$sql = $sqlSelect." ".$sqlFrom." ";
		if($whereClause !== ""){
			$sql .= ' WHERE '.$whereClause;
		}

		switch ($orderBy){
			case 'lemma':
				$orderBy = " l.lemma ";
				break;
			case 'type':
				$orderBy = " t.type ";
				break;
			case 'definition':
				$orderBy = " d.definition ";
				break;
			case 'lexical':
				$orderBy = " lx.lexical ";
				break;
			default:
				$orderBy = " l.lemma ";
		}


		$sql .= " ORDER BY".$orderBy." ".$direction;

		$query = utilities\DataAccess::getEntityManager()->createQuery($sql);
		$query->setFirstResult($offset);
		$query->setMaxResults($limit);
		$result = $query->getResult();
		if(count($result) > 0){
			if(! ($result[0] instanceof models\Lemma)){
				throw new exceptions\DomainObjectNotFoundException("wrong result");
			}
		}else{
			throw new exceptions\DomainResultEmptyException("result not found");
		}

		return $result;

	}

}
?>