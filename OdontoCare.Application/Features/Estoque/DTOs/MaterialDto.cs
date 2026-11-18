using System.ComponentModel.DataAnnotations;

namespace OdontoCare.Application.Features.Estoque.DTOs;

public sealed record MaterialDto(int IdMaterial, string NomeMaterial, string? Descricao,
    int Quantidade, int EstoqueMinimo, string? Unidade, bool EstoqueBaixo);
