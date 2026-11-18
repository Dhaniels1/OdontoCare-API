using System.ComponentModel.DataAnnotations;

namespace OdontoCare.Application.Features.Estoque.DTOs;

public sealed record EquipamentoDto(int IdEquipamento, string NomeEquipamento,
    string? Descricao, string? StatusEquipamento);
