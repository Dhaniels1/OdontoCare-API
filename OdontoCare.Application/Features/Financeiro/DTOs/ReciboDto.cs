using System.ComponentModel.DataAnnotations;

namespace OdontoCare.Application.Features.Financeiro.DTOs;

public sealed record ReciboDto(PagamentoResponseDto Pagamento, string Cpf);
