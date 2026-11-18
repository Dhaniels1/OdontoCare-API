using OdontoCare.Application.DTOs;

namespace OdontoCare.Application.Interfaces;

public interface IPacienteService
{
    Task<List<PacienteResponseDto>> GetTodosAsync();
    Task<PacienteResponseDto?> GetPorIdAsync(int id);
    Task<PacienteResponseDto> CriarAsync(PacienteCreateDto dto);
    Task<PacienteResponseDto?> AtualizarAsync(int id, PacienteUpdateDto dto);
    Task<bool> ExcluirAsync(int id);
}
