using OdontoCare.Application.Features.Financeiro.DTOs;

namespace OdontoCare.Application.Features.Financeiro.Interfaces;

public interface IOrcamentoService
{
    Task<List<OrcamentoResponseDto>> ListarAsync();
    Task<OrcamentoResponseDto?> BuscarAsync(int id);
    Task<OrcamentoResponseDto?> GravarAsync(int? id, OrcamentoGravarDto dto);
    Task<bool> ExcluirAsync(int id);
}
