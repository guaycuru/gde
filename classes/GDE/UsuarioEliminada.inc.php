<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * UsuariosEliminada
 *
 * @ORM\Table(name="gde_usuarios_eliminadas", uniqueConstraints={@ORM\UniqueConstraint(name="id_usuario_sigla", columns={"id_usuario", "sigla"})})
 * @ORM\Entity
 */
class UsuarioEliminada extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_eliminada;

	/**
	 * @var Usuario
	 *
	 * @ORM\ManyToOne(targetEntity="Usuario")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="id_usuario", referencedColumnName="id_usuario")
	 * })
	 */
	protected $usuario;

	/**
	 * @var Disciplina
	 *
	 * @ORM\ManyToOne(targetEntity="Disciplina")
	 * @ORM\JoinColumn(name="sigla", referencedColumnName="sigla")
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
	 * @ORM\Column(type="boolean", nullable=false)
	 */
	protected $parcial = false;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="boolean", nullable=false)
	 */
	protected $proficiencia = false;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=1, nullable=true)
	 */
	protected $tipo;

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
		if($this->getDisciplina(true)->getSigla(false) == $Disciplina->getSigla(false))
			return array($this);
		foreach($Disciplina->getEquivalentes(false) as $Conjunto) {
			if(!isset($Conjunto[$this->getDisciplina(true)->getSigla(false)])) // Esta nao esta neste conjunto, entao nem continuo...
				continue;
			$ret = array();
			foreach($Conjunto as $sigla => $Disci) {
				if($sigla == $this->getDisciplina(true)->getSigla(false))
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
	 * @param $Faltantes
	 * @param $Possiveis
	 * @return array|false
	 */
	public function Elimina_Eletiva(&$Faltantes, $Possiveis) {
		$creditos = 0;
		$ret = array('creditos' => 0, 'diff_creditos' => 0, 'siglas' => array());
		foreach($Faltantes as $c => $Faltante) {
			// Cria (ou usa uma copia anterior) desta CurriculoEletiva
			$Faltantes[$c] = $Faltante = $Faltante->Copia();
			$Conjunto = $Faltante->getConjuntos(true);
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
						$creditos = $Faltante->getCreditos();
						$creditos -= $Disciplina->getCreditos();
						if($creditos <= 0)
							unset($Faltantes[$c]);
						else {
							$Conjunto->remove($indice);
							$Faltantes[$c]->setConjuntos($Conjunto);
							$Faltantes[$c]->setCreditos($creditos);
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
						$creditos = $Faltante->getCreditos();
						$creditos -= $ret['creditos'];
						if($creditos <= 0) {
							$ret['diff_creditos'] = $creditos * -1;
							unset($Faltantes[$c]);
						} else
							$Faltantes[$c]->setCreditos($creditos);
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
	 * @param $A
	 * @param $B
	 * @return int
	 */
	public static function Ordenar_DAC($A, $B) {
		$pa = intval($A->getPeriodo(true)->getID());
		$pb = intval($B->getPeriodo(true)->getID());
		if(($pa > 0) && ($pa % 10 == 0))
			$pa += 3;
		if(($pb > 0) && ($pb % 10 == 0))
			$pb += 3;
		if($pa != $pb)
			return ($pa - $pb);
		else
			return strcmp(
				strtolower($A->getDisciplina(true)->getSigla(false)),
				strtolower($B->getDisciplina(true)->getSigla(false))
			);
	}

	/**
	 * Ordernar_Creditos
	 *
	 * Orderna por periodo e depois por numero de creditos
	 *
	 * @param $A
	 * @param $B
	 * @return int
	 */
	public static function Ordenar_Creditos($A, $B) {
		$pa = intval($A->getPeriodo(true)->getID());
		$pb = intval($B->getPeriodo(true)->getID());
		if(($pa > 0) && ($pa % 10 == 0))
			$pa += 3;
		if(($pb > 0) && ($pb % 10 == 0))
			$pb += 3;
		$ca = intval($A->getDisciplina(true)->getCreditos());
		$cb = intval($B->getDisciplina(true)->getCreditos());
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
