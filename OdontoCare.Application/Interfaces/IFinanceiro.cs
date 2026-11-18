using OdontoCare.Application.DTOs;

namespace OdontoCare.Application.Interfaces;

public interface IProcedimentoService
{
    Task<List<ProcedimentoDetalheDto>> ListarAsync();
    Task<ProcedimentoDetalheDto?> BuscarAsync(int id);
    Task<ProcedimentoDetalheDto?> GravarAsync(int? id, ProcedimentoGravarDto dto);
    Task<bool> ExcluirAsync(int id);
}

public interface IOrcamentoService
{
    Task<List<OrcamentoResponseDto>> ListarAsync();
    Task<OrcamentoResponseDto?> BuscarAsync(int id);
    Task<OrcamentoResponseDto?> GravarAsync(int? id, OrcamentoGravarDto dto);
    Task<bool> ExcluirAsync(int id);
}

public interface IPagamentoService
{
    Task<List<PagamentoResponseDto>> ListarAsync(int? consultaId = null);
    Task<PagamentoResponseDto?> BuscarAsync(int id);
    Task<PagamentoResponseDto?> GravarAsync(int? id, PagamentoGravarDto dto);
    Task<bool> ExcluirAsync(int id);
    Task<ReciboDto?> ReciboAsync(int id);
}

public interface IPainelService
{
    Task<PainelDto> ObterAsync(int? usuarioId, DateTime hoje);
}
