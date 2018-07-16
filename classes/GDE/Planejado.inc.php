<?php

namespace GDE;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * Planejado
 *
 * @ORM\Table(
 *   name="gde_planejados",
 *   indexes={
 *     @ORM\Index(name="compartilhado", columns={"compartilhado"}),
 *     @ORM\Index(name="simulado", columns={"simulado"})
 *   }
 * )
 * @ORM\Entity
 */
class Planejado extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
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
	 * @ORM\Column(type="boolean", options={"default"=0}, nullable=false)
	 */
	protected $compartilhado = false;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean", options={"default"=0}, nullable=false)
	 */
	protected $simulado = false;

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
			return self::Novo($Usuario, $periodo, $periodo_atual, true);
		} else
			return $Planejados[0];
	}

	public static function Novo($Usuario, $periodo, $periodo_atual = null, $flush = true) {
		$Novo = new self();
		$Novo->setUsuario($Usuario);
		$Novo->setPeriodo(Periodo::Load($periodo));
		if($periodo_atual == null) {
			$Atual = Periodo::getAtual();
			$periodo_atual = $Atual->getPeriodo();
		} else
			$Atual = Periodo::Load($periodo_atual);
		$Novo->setPeriodo_Atual($Atual);
		if($Usuario->getAluno(false) !== null) {
			foreach($Usuario->getAluno()->getOferecimentos($periodo_atual) as $Atual)
				if($Atual->getDisciplina(false) !== null)
					$Novo->Adicionar_Eliminada($Atual->getDisciplina(), false, false);
		}
		return ($Novo->Save($flush) !== false) ? $Novo : false;
	}

	// Metodo que passa no cheap check do ProxyGenerator
	public function getId_planejado() {
		return $this->id_planejado;
	}

	public function Adicionar_Oferecimento(Oferecimento $Oferecimento, Arvore $Arvore, $salvar = true) {
		$obs = '';
		if($Arvore->Pode_Cursar($Oferecimento->getDisciplina(), $obs) === false)
			return array('ok' => false, 'Removido' => false, 'motivo' => 'nao_pode_cursar: '.$obs);
		$Tem = $this->Tem_Oferecimento($Oferecimento, true);
		if($Tem !== false) {
			if($Tem->getID() == $Oferecimento->getID()) // Ta tentando adicionar uma que ja esta la!
				return array('ok' => false, 'Removido' => false, 'motivo' => 'ja_esta');
			else // Ta tentando adicionar uma outra turma
				$Removido = array('id' => $Tem->getID());
		} else
			$Removido = false;

		// Marca o Oferecimento como read only
		$Oferecimento->markReadOnly();
		
		if((isset($Removido)) && (!empty($Removido['id'])))
			$this->removeOferecimentos(Oferecimento::Load($Removido['id']));
		$this->addOferecimentos($Oferecimento);
		if($salvar === false)
			$ok = true;
		else
			$ok = $this->Save(true) !== false;
		return array('ok' => $ok, 'Removido' => $Removido);
	}

	public function Remover_Oferecimento(Oferecimento $Oferecimento, $salvar = true) {
		if($this->Tem_Oferecimento($Oferecimento, false) === false)
			return false;

		// Marca o Oferecimento como read only
		$Oferecimento->markReadOnly();

		$this->removeOferecimentos($Oferecimento);
		if($salvar === false)
			return true;
		return ($this->Save(true) !== false);
	}

	public function Tem_Oferecimento(Oferecimento $Oferecimento, $checa_disciplina = false) {
		if($checa_disciplina === false)
			return ($this->getOferecimentos()->contains($Oferecimento)) ? $Oferecimento : false;
		foreach($this->getOferecimentos() as $Of)
			if($Of->getDisciplina()->getSigla(false) == $Oferecimento->getSigla(false))
				return $Of;
		return false;
	}

	public function Adicionar_Eliminada(Disciplina $Disciplina, $parcial = false, $flush = true) {
		$Tem = $this->Tem_Eliminada($Disciplina);
		if($Tem !== false)
			return false;
		$Nova = new PlanejadoEliminada();
		$Nova->setDisciplina($Disciplina);
		$Nova->setParcial($parcial);
		$this->addEliminadas($Nova);
		return ($this->Save($flush) !== false);
	}

	public function Remover_Eliminada(Disciplina $Disciplina, $flush = true) {
		$Eliminada = $this->Tem_Eliminada($Disciplina);
		if($Eliminada === false)
			return false;
		$this->removeEliminadas($Eliminada);
		return ($this->Save($flush) !== false);
	}

	public function Limpar_Eliminadas($flush = true) {
		$this->clearEliminadas();
		return ($this->Save($flush) !== false);
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

	/**
	 * @param Oferecimento $Oferecimento
	 * @return UsuarioAmigo[]
	 */
	public function Amigos_Por_Oferecimento(Oferecimento $Oferecimento) {
		$dql = "SELECT UA FROM GDE\\UsuarioAmigo UA JOIN UA.amigo AS A JOIN A.planejados AS P WHERE :id_oferecimento MEMBER OF P.oferecimentos AND P.simulado = FALSE AND UA.usuario = :id_usuario AND UA.ativo = TRUE";
		return self::_EM()
			->createQuery($dql)
			->setParameters(array('id_oferecimento' => $Oferecimento->getID(), 'id_usuario' => $this->getUsuario()->getID()))
			->getResult();
	}

	/**
	 * @param Oferecimento $Oferecimento
	 * @return int
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public static function Total_Por_Oferecimento(Oferecimento $Oferecimento) {
		$sql = 'SELECT COUNT(DISTINCT P.id_usuario) AS `total` FROM `gde_planejados` AS P '.
				'INNER JOIN `gde_r_planejados_oferecimentos` AS PO ON (PO.`id_planejado` = P.`id_planejado`) '.
				'WHERE P.`simulado` = FALSE '.
				'AND PO.`id_oferecimento` = :id_oferecimento';
		$rsm = new ResultSetMappingBuilder(self::_EM());
		$rsm->addScalarResult('total', 'total');
		$query = self::_EM()->createNativeQuery($sql, $rsm);
		$query->setParameter('id_oferecimento', $Oferecimento->getID());
		return $query->getSingleScalarResult();
	}

	public function Save($flush = true) {
		// ToDo: Fazer isto de uma forma melhor!

		// Marca algumas coisas como read only
		$this->getUsuario()->markReadOnly();
		$this->getPeriodo()->markReadOnly();
		$this->getPeriodo_Atual()->markReadOnly();

		$ok = parent::Save(false) !== false;
		Base::_EM()->flush();
		return $ok;
	}

}
