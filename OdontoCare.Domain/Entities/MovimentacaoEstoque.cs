namespace OdontoCare.Domain.Entities;

public sealed class MovimentacaoEstoque
{
    public int IdMovimentacao { get; set; }
    public int IdMaterial { get; set; }
    public string Tipo { get; set; } = ""; // Entrada | Saida
    public int Quantidade { get; set; }
    public DateTime DataUtc { get; set; }
    public int IdUsuario { get; set; }
}
