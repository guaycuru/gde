<?php

namespace GDE;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * Planejado
 *
 * @ORM\Table(name="gde_planejados", indexes={@ORM\Index(name="compartilhado", columns={"compartilhado"}), @ORM\Index(name="id_usuario", columns={"id_usuario"})})
 * @ORM\Entity
 */
class Planejado extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_planejado;

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
	 * @var Periodo
	 *
	 * @ORM\ManyToOne(targetEntity="Periodo")
	 * @ORM\JoinColumn(name="id_periodo", referencedColumnName="id_periodo")
	 */
	protected $periodo;

	/**
	 * @var Periodo
	 *
	 * @ORM\ManyToOne(targetEntity="Periodo")
	 * @ORM\JoinColumn(name="id_periodo_atual", referencedColumnName="id_periodo")
	 */
	protected $periodo_atual;

	/**
	 * @var ArrayCollection
	 *
	 * @ORM\ManyToMany(targetEntity="Oferecimento")
	 * @ORM\JoinTable(name="gde_r_planejados_oferecimentos",
	 *      joinColumns={@ORM\JoinColumn(name="id_planejado", referencedColumnName="id_planejado")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="id_oferecimento", referencedColumnName="id_oferecimento")}
	 * )
	 */
	protected $oferecimentos;

	/**
	 * @var ArrayCollection
	 *
	 * @ORM\OneToMany(targetEntity="PlanejadoEliminada", mappedBy="planejado", cascade={"persist", "remove"})
	 */
	protected $eliminadas;

	/**
	 * @var ArrayCollection
	 *
	 * @ORM\OneToMany(targetEntity="PlanejadoExtra", mappedBy="planejado", cascade={"persist", "remove"})
	 */
	protected $extras;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean", nullable=false)
	 */
	protected $compartilhado = false;

	private static $_cores = array(
		'#D96666', '#E67399', '#E6804D', '#A7A77D', '#B373B3', '#DDDD00', '#65AD89',
		'#8C66D9', '#C244AB', '#A992A9', '#D1BC36', '#668CB3', '#4650BD', '#59BFB3',
		'#F2A640', '#C7561E', '#94A2BE', '#668CD9', '#B5515D', '#E0C240', '#BE9494'
	);

	/**
	 * Por_Usuario
	 *
	 * @param Usuario $Usuario
	 * @param $periodo
	 * @param bool $compartilhados
	 * @return mixed
	 */
	public static function Por_Usuario(Usuario $Usuario, $periodo, $compartilhados = true) {
		if($periodo instanceof Periodo)
			$periodo = $periodo->getPeriodo();

		$params = array('usuario' => $Usuario->getID(), 'periodo' => $periodo);
		if($compartilhados === true)
			$params['compartilhado'] = true;

		return self::FindBy($params, array('id_planejado' => 'ASC'));
	}

	public static function getCores() {
		return self::$_cores;
	}

	public static function Algum(Usuario $Usuario, $periodo, &$Planejados = null, $periodo_atual = null) {
		if($Planejados == null)
			$Planejados = self::Por_Usuario($Usuario, $periodo, false);
		if(count($Planejados) == 0) {
			if($periodo_atual == null)
				$periodo_atual = Dado::Pega_Dados('planejador_periodo_atual');
			return self::Novo($Usuario, $periodo, $periodo_atual);
		} else
			return $Planejados[0];
	}

	public static function Novo($Usuario, $periodo, $periodo_atual = null, $salvar = true) {
		$Novo = new self();
		$Novo->setUsuario($Usuario);
		$Novo->setPeriodo(Periodo::Load($periodo));
		if($periodo_atual == null) {
			$Atual = Periodo::getAtual();
			$periodo_atual = $Atual->getPeriodo();
		} else
			$Atual = Periodo::Load($periodo_atual);
		$Novo->setPeriodo_Atual($Atual);
		foreach($Usuario->getAluno(true)->getOferecimentos($periodo_atual) as $Atual)
			$Novo->Adicionar_Eliminada($Atual->getDisciplina(true));
		return (($salvar === false) || ($Novo->Salvar(true) !== false)) ? $Novo : false;
	}

	public function Adicionar_Oferecimento(Oferecimento $Oferecimento, $salvar = true) {
		if($this->getUsuario(true)->Pode_Cursar($Oferecimento->getDisciplina(true)) === false)
			return array('ok' => false, 'Removido' => false);
		$Tem = $this->Tem_Oferecimento($Oferecimento, true);
		if($Tem !== false) {
			if($Tem->getID() == $Oferecimento->getID()) // Ta tentando adicionar uma que ja esta la!
				return array('ok' => false, 'Removido' => false);
			else { // Ta tentando adicionar uma outra turma
				$this->Remover_Oferecimento($Tem);
				$Removido = array('id' => $Tem->getID());
			}
		} else
			$Removido = false;
		$this->addOferecimentos($Oferecimento);
		$ok = (($salvar === false) || ($this->Salvar(true) !== false));
		return array('ok' => $ok, 'Removido' => $Removido);
	}

	public function Remover_Oferecimento(Oferecimento $Oferecimento, $salvar = true) {
		if($this->Tem_Oferecimento($Oferecimento, false) === false)
			return false;
		$this->removeOferecimentos($Oferecimento);
		if($salvar === false)
			return true;
		return ($this->Salvar(true) !== false);
	}

	public function Tem_Oferecimento(Oferecimento $Oferecimento, $checa_disciplina = false) {
		if($checa_disciplina === false)
			return ($this->getOferecimentos()->contains($Oferecimento)) ? $Oferecimento : false;
		foreach($this->getOferecimentos() as $Of)
			if($Of->getDisciplina()->getSigla(false) == $Oferecimento->getSigla(false))
				return $Of;
		return false;
	}

	public function Adicionar_Eliminada(Disciplina $Disciplina, $parcial = false, $salvar = true) {
		$Tem = $this->Tem_Eliminada($Disciplina);
		if($Tem !== false)
			return false;
		$Nova = new PlanejadoEliminada();
		$Nova->setDisciplina($Disciplina);
		$Nova->setParcial($parcial);
		$this->addEliminadas($Nova);
		if($salvar === false)
			return true;
		return ($this->Salvar(true) !== false);
	}

	public function Remover_Eliminada(Disciplina $Disciplina, $salvar = true) {
		$Eliminada = $this->Tem_Eliminada($Disciplina);
		if($Eliminada === false)
			return false;
		$this->removeEliminadas($Eliminada);
		if($salvar === false)
			return true;
		return ($this->Salvar(true) !== false);
	}

	public function Limpar_Eliminadas($salvar = true) {
		$this->setEliminadas(new ArrayCollection());
		if($salvar === false)
			return true;
		return ($this->Salvar(true) !== false);
	}

	public function Tem_Eliminada(Disciplina $Disciplina) {
		$criteria = Criteria::create()->where(Criteria::expr()->eq("disciplina", $Disciplina));
		$criteria->setMaxResults(1);
		$Tem = $this->getEliminadas()->matching($criteria);
		if($Tem->isEmpty() === true)
			return false;
		return $Tem->first();
	}

	// Soh adiciona, nao salva (obviamente)
	public function Adicionar_Extra(PlanejadoExtra $Extra) {
		return $this->addExtras($Extra);
	}

	// Soh remove, nao salva (obviamente)
	public function Remover_Extra(PlanejadoExtra $Extra) {
		$this->removeExtras($Extra);
	}

	public function Monta_Horario() {
		$Horario = array();
		foreach($this->getOferecimentos() as $Oferecimento) {
			$horarios = $Oferecimento->Lista_Horarios();
			foreach($horarios as $h)
				$Horario[$h[0]][$h[1]][] = array($Oferecimento, $h[2]);
		}
		return $Horario;
	}

}
