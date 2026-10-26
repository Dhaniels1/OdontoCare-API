using System.ComponentModel.DataAnnotations;

namespace OdontoCare.Application.DTOs;

public sealed class MaterialGravarDto
{
    [Required(ErrorMessage = "Nome obrigatório."), StringLength(200, MinimumLength = 2)]
    public string NomeMaterial { get; set; } = "";
    [MaxLength(2000)] public string? Descricao { get; set; }
    [Range(0, int.MaxValue, ErrorMessage = "Estoque mínimo não pode ser negativo.")]
    public int EstoqueMinimo { get; set; }
    [Required(ErrorMessage = "Unidade é obrigatória."), MaxLength(30)]
    public string Unidade { get; set; } = "";
}

public sealed class MovimentacaoCreateDto
{
    [Range(1, int.MaxValue, ErrorMessage = "A quantidade deve ser positiva.")]
    public int Quantidade { get; set; }
}

public sealed record MaterialDto(int IdMaterial, string NomeMaterial, string? Descricao,
    int Quantidade, int EstoqueMinimo, string? Unidade, bool EstoqueBaixo);
public sealed record MovimentacaoDto(int IdMovimentacao, int IdMaterial, string Tipo,
    int Quantidade, DateTime DataUtc, int IdUsuario);
public sealed record PaginaDto<T>(IReadOnlyList<T> Items, int Pagina, int Tamanho, int Total);

public sealed class EquipamentoGravarDto
{
    [Required, MaxLength(200)] public string NomeEquipamento { get; set; } = "";
    [MaxLength(2000)] public string? Descricao { get; set; }
    [MaxLength(50)] public string? StatusEquipamento { get; set; }
}
public sealed record EquipamentoDto(int IdEquipamento, string NomeEquipamento,
    string? Descricao, string? StatusEquipamento);
