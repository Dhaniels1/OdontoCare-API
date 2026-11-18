using OdontoCare.Application.Features.Consultas.DTOs;

namespace OdontoCare.Application.Features.Consultas.Interfaces;

public interface IConsultaService
{
    Task<List<ConsultaDto>> GetTodasAsync();
    Task<ConsultaDto?> GetPorIdAsync(int id);
    Task<ConsultaDto> CriarAsync(ConsultaCreateDto dto);
    Task<bool> AtualizarAsync(int id, ConsultaUpdateDto dto);
    Task<bool> ExcluirAsync(int id);
    Task<bool> CancelarAsync(int id);
}
