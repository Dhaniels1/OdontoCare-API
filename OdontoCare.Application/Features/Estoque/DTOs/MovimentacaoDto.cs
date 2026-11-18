using System.ComponentModel.DataAnnotations;

namespace OdontoCare.Application.Features.Estoque.DTOs;

public sealed record MovimentacaoDto(int IdMovimentacao, int IdMaterial, string Tipo,
    int Quantidade, DateTime DataUtc, int IdUsuario);
