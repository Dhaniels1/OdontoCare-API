using OdontoCare.Application.Features.Estoque.DTOs;
using OdontoCare.Domain.Entities;

namespace OdontoCare.Application.Features.Estoque.Interfaces;

public interface IEquipamentoService
{
    Task<List<EquipamentoDto>> ListarAsync();
    Task<EquipamentoDto?> BuscarAsync(int id);
    Task<EquipamentoDto> CriarAsync(EquipamentoGravarDto dto);
    Task<EquipamentoDto?> AtualizarAsync(int id, EquipamentoGravarDto dto);
    Task<bool> ExcluirAsync(int id);
}
