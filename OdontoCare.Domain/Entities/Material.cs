namespace OdontoCare.Domain.Entities;

public sealed class Material
{
    public int IdMaterial { get; set; }
    public string NomeMaterial { get; set; } = "";
    public string? Descricao { get; set; }
    public int Quantidade { get; set; }
    public int EstoqueMinimo { get; set; }
    public string? Unidade { get; set; }
    public List<MovimentacaoEstoque> Movimentacoes { get; set; } = [];
}
