<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * UsuariosEliminada
 *
 * @ORM\Table(
 *  name="gde_usuarios_eliminadas",
 *  uniqueConstraints={
 *     @ORM\UniqueConstraint(name="usuario_disciplina", columns={"id_usuario", "id_disciplina"})
 *  }
 * )
 * @ORM\Entity
 */
class UsuarioEliminada extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_eliminada;

	/**
	 * @var Usuario
	 *
	 * @ORM\ManyToOne(targetEntity="Usuario")
	 * @ORM\JoinColumn(name="id_usuario", referencedColumnName="id_usuario")
	 */
	protected $usuario;

	/**
	 * @var Disciplina
	 *
	 * @ORM\ManyToOne(targetEntity="Disciplina")
	 * @ORM\JoinColumn(name="id_disciplina", referencedColumnName="id_disciplina")
	 */
	protected $disciplina;

	/**
	 * @var Periodo
	 *
	 * @ORM\ManyToOne(targetEntity="Periodo")
	 * @ORM\JoinColumn(name="id_periodo", referencedColumnName="id_periodo")
	 */
	protected $periodo;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="boolean", options={"default"=0}, nullable=false)
	 */
	protected $parcial = false;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="boolean", options={"default"=0}, nullable=false)
	 */
	protected $proficiencia = false;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=1, nullable=true)
	 */
	protected $tipo;

	/**
	 * @param Usuario $Usuario
	 * @param Disciplina $Disciplina
	 * @return false|null|UsuarioEliminada
	 */
	public static function Por_Unique(Usuario $Usuario, Disciplina $Disciplina) {
		return self::FindOneBy(array('usuario' => $Usuario, 'disciplina' => $Disciplina));
	}

	/**
	 * Elimina
	 *
	 * Verifica se esta eliminada elimina $Disciplina, possivelmente junto com as $Outras
	 *
	 * @param Disciplina $Disciplina
	 * @param $Outras
	 * @return array|bool
	 */
	public function Elimina(Disciplina $Disciplina, $Outras) {
		if($this->getDisciplina(false) === null)
			return false;
		if($this->getDisciplina()->getID() == $Disciplina->getID())
			return array($this);
		// ToDo: Usar ID ao inves de sigla
		foreach($Disciplina->Equivalencias(false) as $Conjunto) {
			if(!isset($Conjunto[$this->getDisciplina()->getSigla(false)])) // Esta nao esta neste conjunto, entao nem continuo...
				continue;
			$ret = array();
			foreach($Conjunto as $sigla => $Disci) {
				if($sigla == $this->getDisciplina()->getSigla(false))
					$ret[] = $this;
				elseif(isset($Outras[$sigla]))
					$ret[] = $Outras[$sigla];
			}
			if(count($ret) == count($Conjunto))
				return $ret;
		}
		return false;
	}

	/**
	 * Elimina_Eletiva
	 *
	 * Dada uma disciplina, verifica se ela elimina total ou parcialmente algum conjunto de eletivas, e a modifica
	 *
	 * @param $Faltantes CurriculoEletiva[]
	 * @param $Possiveis UsuarioEliminada[]
	 * @param $lcreditos array()
	 * @param $lconjuntos array()
	 * @return array|false
	 */
	public function Elimina_Eletiva(&$Faltantes, $Possiveis, &$lcreditos, &$lconjuntos) {
		// ToDo: Usar ID ao inves de sigla
		$creditos = 0;
		$ret = array('creditos' => 0, 'diff_creditos' => 0, 'siglas' => array());
		foreach($Faltantes as $c => $Faltante) {
			// Cria (ou usa uma copia anterior) desta CurriculoEletiva
			// ToDo: Adianta substituir por ->markReadOnly() ?
			$Faltantes[$c] = $Faltante;// = $Faltante->Copia();
			$Faltante->markReadOnly();
			$falthash = spl_object_hash($Faltante);
			if(!isset($lconjuntos[$falthash]))
				$lconjuntos[$falthash] = clone $Faltante->getConjuntos(true);
			$Conjunto =  $lconjuntos[$falthash];
			if(!isset($lcreditos[$falthash]))
				$lcreditos[$falthash] = $Faltante->getCreditos();
			$faltcreditos = $lcreditos[$falthash];
			foreach($Conjunto as $indice => $Bloco) {
				$sigla = $Bloco->getSigla(false);
				$Disciplina = $Bloco->getDisciplina(true);
				if($Bloco->Fechada() === true) { // Fechada
					$Eliminadas = $this->Elimina($Disciplina, $Possiveis);
					if($Eliminadas !== false) {
						foreach($Eliminadas as $Eli) {
							$ret['creditos'] += $Eli->getDisciplina(true)->getCreditos(false);
							$ret['siglas'][] = $Eli->getDisciplina(true)->getSigla(false);
						}
						// Se ela foi eliminada por equivalencia, e as equivalentes somam mais creditos que a original...
						if($ret['creditos'] > $Disciplina->getCreditos())
							$ret['diff_creditos'] += $creditos - $Disciplina->getCreditos();
						$creditos = $faltcreditos;
						$creditos -= $Disciplina->getCreditos();
						if($creditos <= 0)
							unset($Faltantes[$c]);
						else {
							$Conjunto->remove($indice);
							//$lconjuntos[$falthash]->removeElement($Bloco);
							$lcreditos[$falthash] = $creditos;
						}
						$ret['eliminada'] = $sigla;
						$ret['sobraram'] = $creditos;
						return $ret;
					}
				} else { // Livre ou semi-livre
					if(CurriculoEletiva::Bate_Eletiva($sigla, $this->getDisciplina(true)->getSigla(false), true)) {
						$ret['creditos'] = $this->getDisciplina(true)->getCreditos();
						$ret['siglas'] = array($this->getDisciplina(true)->getSigla(false));
						$ret['eliminada'] = $sigla;
						$creditos = $faltcreditos;
						$creditos -= $ret['creditos'];
						if($creditos <= 0) {
							$ret['diff_creditos'] = $creditos * -1;
							unset($Faltantes[$c]);
						} else
							$lcreditos[$falthash] = $creditos;
						$ret['sobraram'] = $creditos;
						return $ret;
					}
				}
			}
		}
		return false;
	}

	/**
	 * Ordernar_DAC
	 *
	 * Ordena lista de eletivas na ordem da DAC (periodo e depois sigla)
	 *
	 * @param $A UsuarioEliminada
	 * @param $B UsuarioEliminada
	 * @return int
	 */
	public static function Ordenar_DAC(UsuarioEliminada $A, UsuarioEliminada $B) {
		$pa = ($A->getPeriodo(false) !== null) ? intval($A->getPeriodo()->getID()) : 0;
		$pb = ($B->getPeriodo(false) !== null) ? intval($B->getPeriodo()->getID()) : 0;
		if(($pa > 0) && ($pa % 10 == 0))
			$pa += 3;
		if(($pb > 0) && ($pb % 10 == 0))
			$pb += 3;
		if($pa != $pb)
			return ($pa - $pb);
		else {
			$siglaa = ($A->getDisciplina(false) !== null) ? $A->getDisciplina()->getSigla(false) : '';
			$siglab = ($B->getDisciplina(false) !== null) ? $B->getDisciplina()->getSigla(false) : '';
			return strcmp(strtolower($siglaa), strtolower($siglab));
		}
	}

	/**
	 * Ordernar_Creditos
	 *
	 * Orderna por periodo e depois por numero de creditos
	 *
	 * @param $A UsuarioEliminada
	 * @param $B UsuarioEliminada
	 * @return int
	 */
	public static function Ordenar_Creditos(UsuarioEliminada $A, UsuarioEliminada $B) {
		$pa = ($A->getPeriodo(false) !== null) ? intval($A->getPeriodo()->getID()) : 0;
		$pb = ($B->getPeriodo(false) !== null) ? intval($B->getPeriodo()->getID()) : 0;
		if(($pa > 0) && ($pa % 10 == 0))
			$pa += 3;
		if(($pb > 0) && ($pb % 10 == 0))
			$pb += 3;
		$ca = ($A->getDisciplina(false) !== null) ? intval($A->getDisciplina()->getCreditos()) : 0;
		$cb = ($B->getDisciplina(false) !== null) ? intval($B->getDisciplina()->getCreditos()): 0;
		if($ca != $cb)
			return ($cb - $ca);
		else
			return ($pa - $pb);
	}

	/**
	 * toOld
	 *
	 * Retorna este objeto no formato antigo
	 *
	 * @return array
	 */
	public function toOld() {
		return array($this->getDisciplina(true), $this->getProficiencia(false), $this->getParcial(false));
	}

}
