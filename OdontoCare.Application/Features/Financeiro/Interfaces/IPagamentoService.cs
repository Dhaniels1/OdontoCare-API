using OdontoCare.Application.Features.Financeiro.DTOs;

namespace OdontoCare.Application.Features.Financeiro.Interfaces;

public interface IPagamentoService
{
    Task<List<PagamentoResponseDto>> ListarAsync(int? consultaId = null);
    Task<PagamentoResponseDto?> BuscarAsync(int id);
    Task<PagamentoResponseDto?> GravarAsync(int? id, PagamentoGravarDto dto);
    Task<bool> ExcluirAsync(int id);
    Task<ReciboDto?> ReciboAsync(int id);
}
